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
     * @ORM\Id()
     * @ORM\Column(type="string", length=255)
     */
    private $slackId;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Answer", mappedBy="responder", orphanRemoval=true)
     */
    private $answers;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="responder")
     */
    private $teamLead;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slackUsername;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $department;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $jobTitle;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fullName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $site;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $team;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
    }

    public function getEmail(): ?string
    {
        return $this->email;
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
        return $this->slackUsername;
    }

    public function getSlackId(): ?string
    {
        return $this->slackId;
    }

    public function setSlackId(string $slackId): self
    {
        $this->slackId = $slackId;

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

    public function getSlackUsername(): ?string
    {
        return $this->slackUsername;
    }

    public function setSlackUsername(?string $slackUsername): self
    {
        $this->slackUsername = $slackUsername;

        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(?string $jobTitle): self
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getSite(): ?string
    {
        return $this->site;
    }

    public function setSite(?string $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function getTeam(): ?string
    {
        return $this->team;
    }

    public function setTeam(?string $team): self
    {
        $this->team = $team;

        return $this;
    }
}
