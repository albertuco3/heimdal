<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="jobtype")
 */
class JobType
{

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $deletionDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $finishes = false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\JobTypeTransition", mappedBy="fromJobType")
     */
    private Collection $jobTypeTransitions;

    /**
     * JobType constructor.
     */
    public function __construct()
    {
        $this->jobTypeTransitions = new ArrayCollection();
    }

    public function getFinishes(): bool
    {
        return $this->finishes;
    }

    public function setFinishes(bool $finishes): void
    {
        $this->finishes = $finishes;
    }

    public function __toString()
    {
        return $this->getDescription();
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $description = null;

    /**
     * @return Collection
     */
    public function getTransitions(): Collection
    {
        return $this->jobTypeTransitions;
    }

    public function addTransition(JobTypeTransition $jobTypeTransition): self
    {
        if (!$this->jobTypeTransitions->contains($jobTypeTransition)) {
            $this->jobTypeTransitions[] = $jobTypeTransition;
            $jobTypeTransition->setFromJobType($this);
        }

        return $this;
    }

    public function removeTransition(JobTypeTransition $jobTypeTransition): self
    {
        if ($this->jobTypeTransitions->contains($jobTypeTransition)) {
            $this->jobTypeTransitions->removeElement($jobTypeTransition);
            // set the owning side to null (unless already changed)
            if ($jobTypeTransition->getFromJobType() === $this) {
                $jobTypeTransition->setFromJobType(null);
            }
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @ORM\Column(type="decimal", nullable=false, name="points_per_completion", options={"default": 0})
     */
    private float $pointsPerCompletion = 0;


    public function getPointsPerCompletion(): float
    {
        return $this->pointsPerCompletion;
    }

    public function setPointsPerCompletion(float $points): self
    {
        $this->pointsPerCompletion = $points;
        return $this;
    }

    public function getDeletionDate(): ?\DateTimeInterface
    {
        return $this->deletionDate;
    }

    public function setDeletionDate(?\DateTimeInterface $deletionDate): void
    {
        $this->deletionDate = $deletionDate;
    }
}