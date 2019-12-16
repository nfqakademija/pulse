<?php

namespace App\Controller;

use App\Entity\Poll;
use App\Entity\Survey;
use App\Entity\User;
use App\Form\NewPollType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Component\Routing\Annotation\Route;

class PollEasyAdminController extends EasyAdminController
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
     * @Route("/easyadmin/poll/new/{adminId}", name="easyadmin_add_poll", methods={"GET", "POST"})
     */
    public function easyAdminAddPoll($adminId)
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
}
