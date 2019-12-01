<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

use App\Entity\User;
use App\Entity\Poll;
use App\Entity\Question;
use App\Entity\Option;

use App\Form\NewPollType;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'someVariable' => 'NFQ Akademija',
        ]);
    }

    /**
     * @Route("/polls/{adminId}", name="polls", methods={"GET", "POST"})
     */
    public function polls(Request $request, KernelInterface $kernelInterface, $adminId)
    {
        $projectDir = $kernelInterface->getProjectDir();

        $envFile = $projectDir . '/.env';

        $botToken = array('');

        $reading = fopen($envFile, 'r');
        while (!feof($reading)) {
            $line = fgets($reading);
            if (stristr($line, 'BOT_TOKEN')) {
                $lineChars = str_split($line);
                for ($i = 0; $i < count($lineChars); $i++) {
                    if ($lineChars[$i] === '"') {
                        while ($i + 1 < count($lineChars)) {
                            $i++;
                            if ($lineChars[$i] !== '"') {
                                $botToken[0] .= $lineChars[$i];
                            } else {
                                break;
                            }
                        }
                    }
                }
                break;
            }
        }
        fclose($reading);

        $form = $this->createFormBuilder($botToken)
            ->add('token', TextType::class, [
                'label' => 'BOT_TOKEN',
                'attr' => [
                    'class' => 'form-control',
                    'value' => $botToken[0],
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newBotToken = $form["token"]->getData();

            $envTmpFile = $projectDir . '/env.tmp';
            $reading = fopen($envFile, 'r');
            $writing = fopen($envTmpFile, 'w');
            $replaced = false;
            while (!feof($reading)) {
                $line = fgets($reading);
                if (stristr($line, 'BOT_TOKEN')) {
                    $line = 'BOT_TOKEN="' . $newBotToken . '"' . "\n";
                    $replaced = true;
                }
                fputs($writing, $line);
            }
            fclose($reading);
            fclose($writing);
            if ($replaced) {
                rename($envTmpFile, $envFile);
            } else {
                unlink($envTmpFile);
            }

            return $this->redirectToRoute('polls', ['adminId' => $adminId]);
        }

        $entityManager = $this->getDoctrine()->getManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $query = $queryBuilder->select(array('p'))
            ->from('App:Poll', 'p')
            ->where($queryBuilder->expr()->eq('p.user', $adminId))
            ->getQuery();

        $polls = $query->getResult();

        return $this->render('home/polls.html.twig', [
            'title' => 'Polls',
            'form' => $form->createView(),
            'polls' => $polls,
        ]);
    }

    /**
     * @Route("/poll/new/{adminId}", name="add_poll", methods={"GET", "POST"})
     */
    public function addPoll($adminId)
    {
        $admin = $this->getDoctrine()->getRepository(User::class)->find($adminId);

        $entityManager = $this->getDoctrine()->getManager();

        $poll = new Poll();
        $poll->setName('New Poll');
        $poll->setUser($admin);

        $entityManager->persist($poll);
        $entityManager->flush();

        return $this->redirectToRoute('show_poll', ['id' => $poll->getId()]);
    }

    /**
     * @Route("/poll/{id}", name="show_poll", methods={"GET", "POST"})
     */
    public function showPoll(Request $request, $id)
    {
        $poll = $this->getDoctrine()->getRepository(Poll::class)->find($id);

        $form = $this->createForm(NewPollType::class, $poll);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
        }

        return $this->render('home/poll.html.twig', [
            'title' => 'Poll',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/poll/{pollId}/new/question/{questionNumber}", name="add_poll_question", methods={"GET", "POST"})
     */
    public function addPollQuestion($pollId, $questionNumber)
    {
        $poll = $this->getDoctrine()->getRepository(Poll::class)->find($pollId);

        $entityManager = $this->getDoctrine()->getManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $query = $queryBuilder->select(array('q'))
            ->from('App:Question', 'q')
            ->where($queryBuilder->expr()->eq('q.poll', $pollId))
            ->orderBy('q.question_number', 'ASC')
            ->getQuery();

        $pollQuestions = $query->getResult();

        if (empty($pollQuestions)) {
            $question = new Question();
            $question->setPoll($poll);
            $question->setQuestion('New Question');
            $question->setQuestionNumber(1);

            $entityManager->persist($question);
            $entityManager->flush();
        } else {
            foreach ($pollQuestions as $pollQuestion) {
                $pollQuestionNumber = $pollQuestion->getQuestionNumber();
                if ($pollQuestionNumber > intval($questionNumber)) {
                    $pollQuestion->setQuestionNumber($pollQuestionNumber + 1);
                }
            }

            $question = new Question();
            $question->setPoll($poll);
            $question->setQuestion('New Question');
            $question->setQuestionNumber(intval($questionNumber) + 1);

            $entityManager->persist($question);
            $entityManager->flush();
        }

        return $this->redirectToRoute('show_poll', ['id' => $pollId]);
    }

    /**
     * @Route("/question/delete/{questionId}", methods={"DELETE"})
     */
    public function deletePollQuestion($questionId)
    {
        $question = $this->getDoctrine()->getRepository(Question::class)->find($questionId);

        $pollId = $question->getPoll()->getId();

        $entityManager = $this->getDoctrine()->getManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        // Deletes question options
        $query = $queryBuilder->delete('App:Option', 'o')
            ->where($queryBuilder->expr()->eq('o.question', $questionId))
            ->getQuery();

        $query->execute();

        $query = $queryBuilder->select(array('q'))
            ->from('App:Question', 'q')
            ->where($queryBuilder->expr()->eq('q.poll', $pollId))
            ->orderBy('q.question_number', 'ASC')
            ->getQuery();

        $pollQuestions = $query->getResult();

        $questionNumber = $question->getQuestionNumber();

        if ($pollQuestions[array_key_last($pollQuestions)]->getQuestionNumber() !== $questionNumber) {
            foreach ($pollQuestions as $pollQuestion) {
                $pollQuestionNumber = $pollQuestion->getQuestionNumber();

                if ($pollQuestionNumber > $questionNumber) {
                    $pollQuestion->setQuestionNumber($pollQuestionNumber - 1);
                }
            }
        }

        $entityManager->remove($question);
        $entityManager->flush();

        $response = new Response();
        $response->send();
    }

    /**
     * @Route("/question/{questionId}/new/option", name="add_question_option", methods={"GET", "POST"})
     */
    public function addQuestionOption($questionId)
    {
        $question = $this->getDoctrine()->getRepository(Question::class)->find($questionId);

        $entityManager = $this->getDoctrine()->getManager();

        $option = new Option();
        $option->setQuestion($question);
        $option->setValue('New Option');

        $entityManager->persist($option);
        $entityManager->flush();

        return $this->redirectToRoute('show_poll', ['id' => $question->getPoll()->getId()]);
    }

    /**
     * @Route("/option/delete/{optionId}", methods={"DELETE"})
     */
    public function deleteQuestionOption($optionId)
    {
        $option = $this->getDoctrine()->getRepository(Option::class)->find($optionId);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($option);
        $entityManager->flush();

        $response = new Response();
        $response->send();
    }
}
