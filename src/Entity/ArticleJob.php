<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

class ArticleJob
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private ?User $technician = null;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Article")
     */
    private ?Article $article = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JobType")
     */
    private ?JobType $jobType = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $completionDate = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?\DateTimeInterface $creationDate = null;

    /**
     * @ORM\Column(type="integer")
     */
    private ?int $timeSpent;

    public function getTechnician(): ?User
    {
        return $this->technician;
    }

    public function setTechnician(?User $technician): void
    {
        $this->technician = $technician;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): void
    {
        $this->article = $article;
    }

    public function getJobType(): ?JobType
    {
        return $this->jobType;
    }

    public function setJobType(?JobType $jobType): void
    {
        $this->jobType = $jobType;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompletionDate(): ?\DateTimeInterface
    {
        return $this->completionDate;
    }

    public function setCompletionDate(?\DateTimeInterface $completionDate): void
    {
        $this->completionDate = $completionDate;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(?\DateTimeInterface $creationDate): void
    {
        $this->creationDate = $creationDate;
    }

    public function getTimeSpent(): ?int
    {
        return $this->timeSpent;
    }

    public function setTimeSpent(?int $timeSpent): void
    {
        $this->timeSpent = $timeSpent;
    }
}