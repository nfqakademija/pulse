<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MoodRepository")
 */
class Mood
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
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="Responder")
     * @ORM\JoinColumn(name="responder_id", referencedColumnName="id")
     */
    private $responder;

    /**
     * @ORM\OneToOne(targetEntity="Question")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id")
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

    public function getResponder(): ?Responder
    {
        return $this->responder;
    }

    public function getQuestion(): ?Question
    {
        return $this->question;
    }

    public function setValue(?string $value)
    {
        $this->value = $value;
    }

    public function setResponder(?Responder $responder)
    {
        $this->responder = $responder;
    }

    public function setQuestion(?Question $question)
    {
        $this->question = $question;
    }
}
