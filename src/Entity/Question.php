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
     * @ORM\Column(type="integer")
     */
    private $questionNumber;

    /**
     * @ORM\Column(type="text")
     */
    private $question;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Answer", mappedBy="question")
     */
    private $answers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Option", mappedBy="question", orphanRemoval=true)
     */
    private $options;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Poll", inversedBy="questions")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $poll;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->options = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestionNumber(): ?int
    {
        return $this->questionNumber;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestionNumber(?int $questionNumber)
    {
        $this->questionNumber = $questionNumber;
    }

    public function setQuestion(?string $question)
    {
        $this->question = $question;
    }

    public function setAnswers(?array $answers)
    {
        $this->answers = $answers;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswers(Answer $answers): self
    {
        if (!$this->answers->contains($answers)) {
            $this->answers[] = $answers;
            $answers->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswers(Answer $answers): self
    {
        if ($this->answers->contains($answers)) {
            $this->answers->removeElement($answers);
            // set the owning side to null (unless already changed)
            if ($answers->getQuestion() === $this) {
                $answers->setQuestion(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->question;
    }

    /**
     * @return Collection|Option[]
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(Option $option): self
    {
        if (!$this->options->contains($option)) {
            $this->options[] = $option;
            $option->setQuestion($this);
        }

        return $this;
    }

    public function removeOption(Option $option): self
    {
        if ($this->options->contains($option)) {
            $this->options->removeElement($option);
            // set the owning side to null (unless already changed)
            if ($option->getQuestion() === $this) {
                $option->setQuestion(null);
            }
        }

        return $this;
    }

    public function getPoll(): ?Poll
    {
        return $this->poll;
    }

    public function setPoll(?Poll $poll): self
    {
        $this->poll = $poll;

        return $this;
    }
}
