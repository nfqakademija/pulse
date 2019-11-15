<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\ManyToOne(targetEntity="Poll")
     * @ORM\JoinColumn(name="poll_id", referencedColumnName="id")
     */
    private $poll;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Answer", mappedBy="question")
     */
    private $responses;

    public function __construct()
    {
        $this->responses = new ArrayCollection();
    }

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

    public function getPoll(): ?Poll
    {
        return $this->poll;
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

    public function setPoll(?Poll $poll)
    {
        $this->poll = $poll;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getResponses(): Collection
    {
        return $this->responses;
    }

    public function addResponse(Answer $response): self
    {
        if (!$this->responses->contains($response)) {
            $this->responses[] = $response;
            $response->setQuestion($this);
        }

        return $this;
    }

    public function removeResponse(Answer $response): self
    {
        if ($this->responses->contains($response)) {
            $this->responses->removeElement($response);
            // set the owning side to null (unless already changed)
            if ($response->getQuestion() === $this) {
                $response->setQuestion(null);
            }
        }

        return $this;
    }
    public function __toString() {
        return "This is toString method of question obj";
    }
}
