<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Survey;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SurveyAdminController extends AbstractController
{
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
