<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Form\AnswerType;
use Doctrine\ORM\NoResultException;
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
     * @Route("/api/form/{id}", name="api")
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
     * @Route("/api/answer", name="api/answer")
     * @Method("POST")
     * @return Response
     */
    public function getAnswer(Request $request)
    {
        try {
            $data = json_decode(
                $request->getContent(),
                true
            );
            var_dump($data);
            $form = $this->createForm(AnswerType::class, new Answer());
            $form->submit($data);
            if (false === $form->isValid()) {
                return new JsonResponse(
                    [
                        'status' => 'error',
                    ],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }



            return new JsonResponse(
                [
                    'status' => 'ok',
                ],
                JsonResponse::HTTP_CREATED
            );
        } catch (\Throwable $e) {
            var_dump($e);
        };
    }
}
