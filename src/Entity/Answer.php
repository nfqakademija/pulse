<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AnswerRepository")
 */
class Answer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank()
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Question", inversedBy="responses")
     * @ORM\JoinColumn(nullable=true)
     */
    private $question;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Responder", inversedBy="answers")
     * @ORM\JoinColumn(referencedColumnName="slack_id", nullable=false)
     */
    private $responder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Survey", inversedBy="answers")
     */
    private $survey;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Option", inversedBy="answers")
     */
    private $answerOption;


    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setQuestion(?Question $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function __toString()
    {
        return $this->value;
    }

    public function getResponder(): ?Responder
    {
        return $this->responder;
    }

    public function setResponder(?Responder $responder): self
    {
        $this->responder = $responder;

        return $this;
    }

    public function getSurvey(): ?Survey
    {
        return $this->survey;
    }

    public function setSurvey(?Survey $survey): self
    {
        $this->survey = $survey;

        return $this;
    }

    public function getAnswerOption(): ?Option
    {
        return $this->answerOption;
    }

    public function setAnswerOption(?Option $answerOption): self
    {
        $this->answerOption = $answerOption;

        return $this;
    }
}
