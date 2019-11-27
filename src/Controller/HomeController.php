<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Poll;

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
     * @Route("/polls/{id}", name="polls", methods={"GET"})
     */
    public function polls($id)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $queryBuilder = $entityManager->createQueryBuilder();

        $query = $queryBuilder->select(array('p'))
            ->from('App:Poll', 'p')
            ->where($queryBuilder->expr()->eq('p.user', $id))
            ->getQuery();

        $polls = $query->getResult();

        return $this->render('home/polls.html.twig', [
            'title' => 'Polls',
            'polls' => $polls,
        ]);
    }
}
