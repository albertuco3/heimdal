<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="jobtypetransition", uniqueConstraints={@ORM\UniqueConstraint(name="jobtype_transition_unique_1", columns={"from_job_type_id", "to_job_type_id"})})
 */
class JobTypeTransition
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JobType")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?JobType $fromJobType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JobType")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?JobType $toJobType;

    /**
     * @ORM\Column(type="integer")
     */
    private int $pointsPerCompletion;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getFromJobType(): ?JobType
    {
        return $this->fromJobType;
    }

    public function setFromJobType(?JobType $fromJobType): void
    {
        $this->fromJobType = $fromJobType;
    }

    /**
     * @return mixed
     */
    public function getToJobType(): ?JobType
    {
        return $this->toJobType;
    }

    public function setToJobType(?JobType $toJobType): void
    {
        $this->toJobType = $toJobType;
    }

    public function getPointsPerCompletion(): int
    {
        return $this->pointsPerCompletion;
    }

    public function setPointsPerCompletion(int $pointsPerCompletion): void
    {
        $this->pointsPerCompletion = $pointsPerCompletion;
    }


}