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
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Responder", inversedBy="answers")
     * @ORM\JoinColumn(referencedColumnName="slack_id", onDelete="SET NULL")
     */
    private $responder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Survey", inversedBy="answers")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $survey;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Option", inversedBy="answers")
     * @ORM\JoinColumn(onDelete="CASCADE")
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
