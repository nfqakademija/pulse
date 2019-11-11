<?php

namespace App\Controller;

use App\Entity\Responder;
use App\Form\ResponderType;
use App\Repository\ResponderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/responder")
 */
class ResponderController extends AbstractController
{
    /**
     * @Route("/", name="responder_index", methods={"GET"})
     */
    public function index(ResponderRepository $responderRepository): Response
    {
        return $this->render('responder/index.html.twig', [
            'responders' => $responderRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="responder_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $responder = new Responder();
        $form = $this->createForm(ResponderType::class, $responder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($responder);
            $entityManager->flush();

            return $this->redirectToRoute('responder_index');
        }

        return $this->render('responder/new.html.twig', [
            'responder' => $responder,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="responder_show", methods={"GET"})
     */
    public function show(Responder $responder): Response
    {
        return $this->render('responder/show.html.twig', [
            'responder' => $responder,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="responder_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Responder $responder): Response
    {
        $form = $this->createForm(ResponderType::class, $responder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('responder_index');
        }

        return $this->render('responder/edit.html.twig', [
            'responder' => $responder,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="responder_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Responder $responder): Response
    {
        if ($this->isCsrfTokenValid('delete'.$responder->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($responder);
            $entityManager->flush();
        }

        return $this->redirectToRoute('responder_index');
    }
}
