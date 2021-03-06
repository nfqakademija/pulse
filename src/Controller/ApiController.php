<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Option;
use App\Entity\Question;
use App\Entity\Responder;
use App\Entity\Survey;
use App\Form\AnswerType;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
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
     * @Rest\Get("api/answers")
     * @return Response
     */
    public function getAnswer()
    {
        $repository = $this->getDoctrine()->getRepository(Answer::class);
        $answers = $repository->findall();
        return $this->handleView($this->view($answers));
    }

    /**
     * @Rest\Get("api/responders/{id}")
     * @return Response
     */
    public function getResponders($id)
    {
        $repository = $this->getDoctrine()->getRepository(Responder::class);
        $responders = $repository->findRespondersSlackIdByAdminId($id);
        return $this->handleView($this->view($responders));
    }

    /**
     * @Rest\Get("/api/poll/{id}")
     * @return Response
     */
    public function getPollById($id)
    {
        $repository = $this->getDoctrine()->getRepository(Option::class);
        $poll = $repository->findOneQuestionId($id);
        return $this->handleView($this->view($poll));
    }

    /**
     * @Route("/api/get/full/survey/{id}", name="api/get/full/poll")
     * @param $id
     * @return array
     */
    public function sendForm($id)
    {
        $data = [];

        $repository = $this->getDoctrine()->getRepository(Survey::class);
        $survey = $repository->findOneByPollId($id);
        array_push($data, $survey);

        $repository = $this->getDoctrine()->getRepository(Question::class);
        $questions = $repository->findOneByPollId($survey['pollId']);
        array_push($data, $questions);
        $options = [];
        $repository = $this->getDoctrine()->getRepository(Option::class);
        foreach ($questions as $question) {
            $option = $repository->findOneByQuestionId($question['id']);
            array_push($options, $option);
        }
        array_push($data, $options);
        return $data;
    }

    /**
     * @Rest\Post("api/store_answer")
     * @param Request $request
     * @return Response
     */
    public function postAnswer(Request $request)
    {
        // $dotenv = new Dotenv();
        // $dotenv->load(__DIR__ . '/.env');
        $SLACK_SIGNING_SECRET = "13b9b250f27abf997bb8e9b869138458"; // getenv("SLACK_SIGNING_SECRET");
        $answer = new Answer();
        $form = $this->createForm(AnswerType::class, $answer);
        $raw_body = file_get_contents('php://input');
        $body = urldecode($_POST['payload']);
        $message = json_decode($body, true);


        if (empty($_SERVER['HTTP_X_SLACK_SIGNATURE']) || empty($_SERVER['HTTP_X_SLACK_REQUEST_TIMESTAMP'])) {
            header('HTTP/1.1 400 Bad Request', true, 400);
            exit;
        } else {
            $version = explode("=", $_SERVER['HTTP_X_SLACK_SIGNATURE']);
            $timestamp = $_SERVER['HTTP_X_SLACK_REQUEST_TIMESTAMP'];
            $token = $message['token'];
            $sig_basestring = "{$version[0]}:$timestamp:$raw_body";
            $hash_signature = hash_hmac('sha256', $sig_basestring, $SLACK_SIGNING_SECRET);
            var_dump($_SERVER['HTTP_X_SLACK_SIGNATURE']);
            var_dump($timestamp);
            if (!hash_equals($_SERVER['HTTP_X_SLACK_SIGNATURE'], "v0=" . $hash_signature)) {
                header('HTTP/1.1 400 Bad Request', true, 400);
                exit;
            }
        }
        if (empty($message['callback_id']) || empty($message['actions'])) {
            header('HTTP/1.1 400 Bad Request', true, 400);
            exit;
        }

        $post_data = (array('value' => $message['actions'][0]['name'],
            'responder' => $message['user']['id'],
            'survey' => $message['callback_id'],
            'answerOption' => $message['actions'][0]['value']));
        $form->submit($post_data);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($answer);
            $em->flush();
            return $this->handleView($this->view(['Geros' => 'dienos'], Response::HTTP_CREATED));
        }
        return $this->handleView($this->view($form->getErrors()));
    }
}
