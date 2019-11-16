<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Responder", inversedBy="answers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $responder;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Question", inversedBy="responses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $question;

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

    public function getResponder(): ?Responder
    {
        return $this->responder;
    }

    public function setResponder(?Responder $responder): self
    {
        $this->responder = $responder;

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
}
