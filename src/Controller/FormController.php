<?php

namespace App\Controller;

use App\Entity\Option;
use App\Entity\Question;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\QuestionOptionType;

class FormController extends AbstractController
{
    /**
     * @Route("/form", name="form")
     */
    public function index()
    {

        $question = new Question();
        $option = new Option();
        $option->setValue("ggg");
        $question->getOptions()->add($option);
        $question->getOptions()->add($option);
        var_dump($option);

        $form = $this->createForm(QuestionOptionType::class, $question);

        return $this->render('form/index.html.twig', [
            'custom_form' => $form->createView(),
        ]);
    }
}
