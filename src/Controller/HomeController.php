<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="about")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'someVariable' => 'Please read it before the journey!',
        ]);
    }
}
