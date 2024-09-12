<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="articlejobtimeentry")
 */
class ArticleJobTimeEntry
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private ?User $technician = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JobType")
     */
    private ?JobType $jobType = null;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Article")
     */
    private ?Article $article = null;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private ?\DateTimeInterface $start;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $end;

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(?\DateTimeInterface $start): void
    {
        $this->start = $start;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(?\DateTimeInterface $end): void
    {
        $this->end = $end;
    }

    public function getJobType(): ?JobType
    {
        return $this->jobType;
    }

    public function setJobType(?JobType $jobType): void
    {
        $this->jobType = $jobType;
    }

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
}
