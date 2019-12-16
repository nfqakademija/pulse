<?php

namespace App\Controller;

use App\Entity\Answer;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;

class SurveyController extends EasyAdminController
{
    public function graphSurveyAction()
    {
        $survey = $this->request->attributes->get('easyadmin')['item'];

        $answers = $this->getDoctrine()->getRepository(Answer::class)->findBySurvey($survey->getId());

        $assocAnswerArray = array();

        foreach ($answers as $answer) {
            $questionNumber = $answer['questionNumber'];

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

        return $this->render('survey/graph.html.twig', [
            'title' => 'Graph',
            'survey' => $survey,
            'answers' => $assocAnswerArray,
        ]);
    }
}
