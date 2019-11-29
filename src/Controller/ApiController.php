<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Form\AnswerType;
use Doctrine\ORM\NoResultException;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApiController
 * @package App\Controller
 */
class ApiController extends AbstractFOSRestController
{
    /**
     * @Route("/api/poll/{id}", name="api")
     * @Method("POST")
     * @param $id
     * @return Response
     */
    public function sendForm($id): Response
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT q,o
            FROM App:Question q
            JOIN q.options o
            WHERE q.poll = :id'
        )->setParameter('id', $id);
        $return = $query->getArrayResult();
        try {
            return new JsonResponse($return);
        } catch (NoResultException $e) {
            return null;
        }
        //'SELECT q,p
        // FROM App:Question q
        // JOIN q.poll p
        // WHERE q.poll = :id'
    }

    /**
     * @Rest\Post("api/store_answer")
     * @param Request $request
     * @return Response
     */
    public function postAnswer(Request $request)
    {
        $answer = new Answer();
        $form = $this->createForm(AnswerType::class, $answer);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($answer);
            $em->flush();
            return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_CREATED));
        }
        return $this->handleView($this->view($form->getErrors()));
    }

    /**
     * @Rest\Get("api/answers")
     * @return Response
     */
    public function getAnswer()
    {
        $repository = $this->getDoctrine()->getRepository(Answer::class);
        $answers = $repository->findall();
        return $this->handleView($this->view($answers));
    }
}

//Good one
//namespace App\Controller;
//
//use App\Entity\Answer;
//use App\Entity\Poll;
//use App\Form\AnswerType;
//use FOS\RestBundle\Controller\AbstractFOSRestController;
//use FOS\RestBundle\Controller\Annotations as Rest;
//use Symfony\Component\HttpFoundation\Request;
//use Symfony\Component\HttpFoundation\Response;
//
//
///**
// * Class ApiController
// * @package App\Controller
// */
//class ApiController extends AbstractFOSRestController
//{
//    /**
//     * @Rest\Get("api/poll/{id}")
//     * @param $id
//     * @return Response
//     */
//    public function getPoll($id): Response
//    {
//        $repository = $this->getDoctrine()->getRepository(Poll::class);
//        $poll = $repository->find($id);
//        return $this->handleView($this->view($poll));
//    }
//
//    /**
//     * @Rest\Post("api/store_answer")
//     * @param Request $request
//     * @return Response
//     */
//    public function postAnswer(Request $request)
//    {
//        $answer = new Answer();
//        $form = $this->createForm(AnswerType::class, $answer);
//        $data = json_decode($request->getContent(), true);
//        $form->submit($data);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $em = $this->getDoctrine()->getManager();
//            $em->persist($answer);
//            $em->flush();
//            return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_CREATED));
//        }
//        return $this->handleView($this->view($form->getErrors()));
//    }
//
//    /**
//     * @Rest\Get("api/answers")
//     * @return Response
//     */
//    public function getAnswer()
//    {
//        $repository = $this->getDoctrine()->getRepository(Answer::class);
//        $answers = $repository->findall();
//        return $this->handleView($this->view($answers));
//    }
//}
