<?php

namespace App\Controller;

use App\Entity\Option;
use App\Entity\Poll;
use App\Entity\Question;
use App\Entity\Survey;
use App\Entity\User;
use App\Form\NewPollType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PollController extends EasyAdminController
{
    public function myListPollAction()
    {
        $polls = $this->getDoctrine()->getRepository(Poll::class)
            ->findByUser($this->getUser()->getId());

        $surveys = $this->getDoctrine()->getRepository(Survey::class)
            ->findByUser($this->getUser()->getId());

        return $this->render('poll/polls.html.twig', [
            'title' => 'Polls',
            'polls' => $polls,
            'surveys' => $surveys,
        ]);
    }

    public function formEditPollAction()
    {
        $form = $this->createForm(NewPollType::class, $this->request->attributes->get('easyadmin')['item']);

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('easyadmin', [
                'entity' => 'Poll',
                'action' => 'myList',
            ]);
        }

        return $this->render('poll/poll.html.twig', [
            'title' => 'Poll',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/poll/new/{adminId}", name="add_poll", methods={"GET", "POST"})
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

        return $this->redirectToRoute('easyadmin', [
            'entity' => 'Poll',
            'action' => 'formEdit',
            'id' => $poll->getId(),
        ]);
    }

    /**
     * @Route("/admin/poll/{pollId}/new/question/{questionId}", name="add_poll_question", methods={"GET", "POST"})
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

        return $this->redirectToRoute('easyadmin', [
            'entity' => 'Poll',
            'action' => 'formEdit',
            'id' => $poll->getId(),
        ]);
    }

    /**
     * @Route("/admin/question/delete/{questionId}", methods={"POST"})
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
     * @Route("/admin/question/{questionId}/new/option", name="add_question_option", methods={"GET", "POST"})
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

        return $this->redirectToRoute('easyadmin', [
            'entity' => 'Poll',
            'action' => 'formEdit',
            'id' => $question->getPoll()->getId(),
        ]);
    }

    /**
     * @Route("/admin/option/delete/{optionId}", methods={"POST"})
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
