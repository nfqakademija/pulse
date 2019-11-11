<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuestionRepository")
 */
class Question
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $question_number;

    /**
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @ORM\Column(type="json")
     */
    private $answers = [];

    /**
     * @ORM\ManyToOne(targetEntity="Form")
     * @ORM\JoinColumn(name="form_id", referencedColumnName="id")
     */
    private $form;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestionNumber(): ?string
    {
        return $this->question_number;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getAnswers(): ?array
    {
        return $this->answers;
    }

    public function getForm(): ?Form
    {
        return $this->form;
    }

    public function setQuestionNumber(?string $question_number)
    {
        $this->question_number = $question_number;
    }

    public function setName(?string $name)
    {
        $this->name = $name;
    }

    public function setAnswers(?array $answers)
    {
        $this->answers = $answers;
    }

    public function setForm(?Form $form)
    {
        $this->form = $form;
    }
}
