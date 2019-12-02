<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Option;
use App\Entity\Question;
use App\Repository\QuestionRepository;
use App\Entity\Poll;
use App\Repository\PollRepository;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChartController extends AbstractController
{

    /**
     * @Route("/chart", name="choose_question", methods={"GET"})
     * @param PollRepository $pollRepository
     * @return Response
     */
    public function chart(PollRepository $pollRepository): Response
    {
        $repository = $this->getDoctrine()->getRepository(Poll::class);
        $polls = $repository->findBy(['user' => $this->getUserID()]);

        return $this->render('chart/charts.html.twig', [
            'questions' => $this->getQuestions($polls),
        ]);
    }
    public function getQuestions($polls){
        $collectionArr = [];
        foreach ($polls as $poll) {
            array_push($collectionArr,$poll->getId());
        }
        $repository = $this->getDoctrine()->getRepository(Question::class);
        return $repository->findBy(['poll' => $collectionArr]);
    }
    /**
     * @Route("/charts/{id}", name="show_chart", methods={"GET"})
     * @param $id
     * @return Response
     */
    public function show_chart($id): Response
    {
        $options = $this->getOptions($id);
        $countedOptions = $this->countAnswers($id,$options);
        $optionsArr = $this->getOptionsArray($countedOptions);
        $pieChart = new PieChart();
        $pieChart->getData()->setArrayToDataTable(
            $optionsArr
        );
        $pieChart->getOptions()->setTitle('Results');
        $pieChart->getOptions()->setHeight(500);
        $pieChart->getOptions()->setWidth(1000);
        $pieChart->getOptions()->getTitleTextStyle()->setBold(true);
        $pieChart->getOptions()->getTitleTextStyle()->setColor('#009900');
        $pieChart->getOptions()->getTitleTextStyle()->setItalic(true);
        $pieChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $pieChart->getOptions()->getTitleTextStyle()->setFontSize(20);

        return $this->render('chart/show_chart.html.twig', array('piechart' => $pieChart,'options' => $optionsArr));
    }
    public function getOptions($id)
    {
        $repository = $this->getDoctrine()->getRepository(Question::class);
        $question = $repository->findBy(['id' => $id]);

        $options = $question[0]->getOptions();
        foreach ( $options as $option)
        {
            $option ->{"quantity"} = 0;
        }
        return $options;
    }
    public function countAnswers($questionId,$options){
        $repository = $this->getDoctrine()->getRepository(Answer::class);
        $answers = $repository->findBy(['question' => $questionId]);

        foreach ($options as $option)
        {
            foreach ($answers as $answer)
            {
                if($answer-> getValue() == $option-> getId())
                {
                    $option->quantity = $option->quantity +1;
                }
            }
        }
        return $options;
    }
    public function getOptionsArray($options){
        $collectionArr = [['Answer','Voters']];
        foreach ($options as $option) {
            $stack = array($option->getValue(),$option->quantity);
            array_push($collectionArr,$stack);
        }
    return $collectionArr;
    }

    public function getUserID()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        return $user->getId();;
    }
}
