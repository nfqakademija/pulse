<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use App\Entity\User;
use App\Form\UserType;
use App\Entity\Poll;
use App\Form\PollType;
use App\Repository\PollRepository;
use App\Entity\Question;
use App\Form\QuestionType;
use App\Entity\Option;
use App\Form\OptionType;


class ApiController extends AbstractController
{
    /**
     * @Route("/api/{id}", name="api")
     * @Method("POST")
     * @param $id
     * @return Response
     */
    public function index($id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT c,p
            FROM App:Question c 
            JOIN c.options p
            WHERE p.question = :id'
        )->setParameter('id', $id);
        $return = $query->getArrayResult();
        try {
            return new JsonResponse($return);
        } catch (\Doctrine\ORM\NoResultException $e) {
            return null;
        }

    }
}
