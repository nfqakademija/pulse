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
use App\Entity\Survey;
use App\Entity\Poll;
use App\Entity\Question;
use App\Entity\Option;
use App\Entity\Answer;

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

        $botSettings = array('token' => '', 'signingSecret' => '');

        $reading = fopen($envFile, 'r');

        while (!feof($reading)) {
            $line = fgets($reading);

            if (stristr($line, 'BOT_TOKEN') || stristr($line, 'SLACK_SIGNING_SECRET')) {
                $lineChars = str_split($line);

                for ($i = 0; $i < count($lineChars); $i++) {
                    if ($lineChars[$i] === '"') {
                        while ($i + 1 < count($lineChars)) {
                            $i++;

                            if ($lineChars[$i] !== '"') {
                                if (stristr($line, 'BOT_TOKEN')) {
                                    $botSettings['token'] .= $lineChars[$i];
                                } else {
                                    $botSettings['signingSecret'] .= $lineChars[$i];
                                }
                            } else {
                                break;
                            }
                        }
                        break;
                    }
                }
            }
        }

        fclose($reading);

        $form = $this->createFormBuilder($botSettings)
            ->add('token', TextType::class, [
                'label' => 'BOT_TOKEN',
                'attr' => [
                    'class' => 'form-control',
                    'value' => $botSettings['token'],
                    'style' => 'margin-bottom: 20px;',
                    'pattern' => '[a-zA-Z0-9_-]+',
                ],
            ])
            ->add('signingSecret', TextType::class, [
                'label' => 'SLACK_SIGNING_SECRET',
                'attr' => [
                    'class' => 'form-control',
                    'value' => $botSettings['signingSecret'],
                    'style' => 'margin-bottom: 20px;',
                    'pattern' => '[a-zA-Z0-9_-]+',
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
            $newToken = $form["token"]->getData();

            $newSigningSecret = $form["signingSecret"]->getData();

            $envTmpFile = $projectDir . '/env.tmp';

            $reading = fopen($envFile, 'r');

            $writing = fopen($envTmpFile, 'w');

            $replaced = false;

            while (!feof($reading)) {
                $line = fgets($reading);

                if (stristr($line, 'BOT_TOKEN')) {
                    if (preg_match('/^[a-zA-Z0-9_-]+$/', $newToken)) {
                        $line = 'BOT_TOKEN="' . $newToken . '"' . "\n";
                    } else {
                        $line = 'BOT_TOKEN="invalid"' . "\n";
                    }

                    $replaced = true;
                } else if (stristr($line, 'SLACK_SIGNING_SECRET')) {
                    if (preg_match('/^[a-zA-Z0-9_-]+$/', $newSigningSecret)) {
                        $line = 'SLACK_SIGNING_SECRET="' . $newSigningSecret . '"' . "\n";
                    } else {
                        $line = 'SLACK_SIGNING_SECRET="invalid"' . "\n";
                    }

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

        $polls = $this->getDoctrine()->getRepository(Poll::class)->findByUser($adminId);

        $surveys = $this->getDoctrine()->getRepository(Survey::class)->findByUser($adminId);

        return $this->render('home/polls.html.twig', [
            'title' => 'Polls',
            'form' => $form->createView(),
            'polls' => $polls,
            'surveys' => $surveys,
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

            return $this->redirectToRoute('polls', ['adminId' => $poll->getUser()->getId()]);
        }

        return $this->render('home/poll.html.twig', [
            'title' => 'Poll',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/poll/{pollId}/new/question/{questionId}", name="add_poll_question", methods={"GET", "POST"})
     */
    public function addPollQuestion($pollId, $questionId)
    {
        $poll = $this->getDoctrine()->getRepository(Poll::class)->find($pollId);

        $entityManager = $this->getDoctrine()->getManager();

        $pollQuestions = $this->getDoctrine()->getRepository(Question::class)->findByPoll($pollId);

        $newQuestion = new Question();
        $newQuestion->setPoll($poll);
        $newQuestion->setQuestion('New Question');

        if (empty($pollQuestions)) {
            $newQuestion->setQuestionNumber(1);
        } else {
            $question = $this->getDoctrine()->getRepository(Question::class)->find($questionId);

            $questionNumber = $question->getQuestionNumber();

            foreach ($pollQuestions as $pollQuestion) {
                $pollQuestionNumber = $pollQuestion->getQuestionNumber();

                if ($pollQuestionNumber > $questionNumber) {
                    $pollQuestion->setQuestionNumber($pollQuestionNumber + 1);
                }
            }

            $newQuestion->setQuestionNumber($questionNumber + 1);
        }

        $entityManager->persist($newQuestion);
        $entityManager->flush();

        return $this->redirectToRoute('show_poll', ['id' => $pollId]);
    }

    /**
     * @Route("/question/delete/{questionId}", methods={"POST"})
     */
    public function deletePollQuestion($questionId)
    {
        $question = $this->getDoctrine()->getRepository(Question::class)->find($questionId);

        $pollId = $question->getPoll()->getId();

        $entityManager = $this->getDoctrine()->getManager();

        $pollQuestions = $this->getDoctrine()->getRepository(Question::class)->findByPoll($pollId);

        $questionNumber = $question->getQuestionNumber();

        if ($pollQuestions[array_key_last($pollQuestions)]->getQuestionNumber() !== $questionNumber) {
            foreach ($pollQuestions as $pollQuestion) {
                $pollQuestionNumber = $pollQuestion->getQuestionNumber();

                if ($pollQuestionNumber > $questionNumber) {
                    $pollQuestion->setQuestionNumber($pollQuestionNumber - 1);
                }
            }
        }

        $this->getDoctrine()->getRepository(Option::class)->deleteByQuestion($questionId);

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
     * @Route("/option/delete/{optionId}", methods={"POST"})
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

    /**
     * @Route("/graph/{surveyId}", name="graph", methods={"GET"})
     */
    public function graph($surveyId)
    {
        $survey = $this->getDoctrine()->getRepository(Survey::class)->find($surveyId);

        $answers = $this->getDoctrine()->getRepository(Answer::class)->findBySurvey($surveyId);

        $assocAnswerArray = array();

        foreach ($answers as $answer) {
            $questionNumber = $answer['question_number'];

            $question = $answer['question'];

            $optionId = $answer['optionId'];

            $optionValue = $answer['value'];

            $optionCount = $answer['count'];

            if (!array_key_exists($questionNumber, $assocAnswerArray)) {
                $assocAnswerArray[$questionNumber] = array();
                $assocAnswerArray[$questionNumber]['question'] = $question;
                $assocAnswerArray[$questionNumber]['options'] = array();
            }

            $assocAnswerArray[$questionNumber]['options'][$optionId] = array();
            $assocAnswerArray[$questionNumber]['options'][$optionId]['value'] = $optionValue;
            $assocAnswerArray[$questionNumber]['options'][$optionId]['count'] = $optionCount;
        }

        return $this->render('home/graph.html.twig', [
            'title' => 'Graph',
            'survey' => $survey,
            'answers' => $assocAnswerArray,
        ]);
    }
}
