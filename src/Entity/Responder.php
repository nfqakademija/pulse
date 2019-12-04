<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ResponderRepository")
 */
class Responder
{
    /**
     * @ORM\Column(type="text")
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $email;


    /**
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     */
    private $slack_id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Answer", mappedBy="responder", orphanRemoval=true)
     */
    private $answers;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="responder")
     */
    private $teamLead;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setName(?string $name)
    {
        $this->name = $name;
    }

    public function setEmail(?string $email)
    {
        $this->email = $email;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setResponder($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
            // set the owning side to null (unless already changed)
            if ($answer->getResponder() === $this) {
                $answer->setResponder(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getslack_id(): ?string
    {
        return $this->slack_id;
    }

    public function getSlackId(): ?string
    {
        return $this->slack_id;
    }

    public function setSlackId(string $slack_id): self
    {
        $this->slack_id = $slack_id;

        return $this;
    }

    public function getTeamLead(): ?User
    {
        return $this->teamLead;
    }

    public function setTeamLead(?User $teamLead): self
    {
        $this->teamLead = $teamLead;

        return $this;
    }
}
