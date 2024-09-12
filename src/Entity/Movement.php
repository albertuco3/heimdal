<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="movement")
 */
class Movement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private \DateTimeInterface $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Article")
     * @ORM\JoinColumn(name="article_id", referencedColumnName="id")
     */
    private ?Article $article = null;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="responsible_id", referencedColumnName="id")
     */
    private ?User $responsibleUser = null;

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }

    public function setArticle(?Article $article): self
    {
        $this->article = $article;
        return $this;
    }

    public function getNewOwner(): ?User
    {
        return $this->newOwner;
    }

    public function setNewOwner($newOwner): self
    {
        $this->newOwner = $newOwner;
        return $this;
    }


    public function getOldOwner(): ?User
    {
        return $this->oldOwner;
    }

    public function setOldOwner($oldOwner): self
    {
        $this->oldOwner = $oldOwner;
        return $this;
    }

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="new_owner", referencedColumnName="id")
     */
    private $newOwner;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="old_owner", referencedColumnName="id")
     */
    private $oldOwner;


    public function getNewJobType(): ?JobType
    {
        return $this->newJobType;
    }

    public function setNewJobType($newJobType): self
    {
        $this->newJobType = $newJobType;
        return $this;
    }

// Setters

    public function getOldJobType(): ?JobType
    {
        return $this->oldJobType;
    }

    public function setOldJobType($oldJobType): self
    {
        $this->oldJobType = $oldJobType;
        return $this;
    }


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="new_receiver", referencedColumnName="id")
     */
    private $newReceiver;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="old_receiver", referencedColumnName="id")
     */
    private $oldReceiver;


    public function getOldReceiver(): ?User
    {
        return $this->oldReceiver;
    }

// Setters

    public function setOldReceiver($oldReceiver): self
    {
        $this->oldReceiver = $oldReceiver;
        return $this;
    }

    public function getNewReceiver(): ?User
    {
        return $this->newReceiver;
    }

    public function setNewReceiver($newReceiver): self
    {
        $this->newReceiver = $newReceiver;
        return $this;
    }

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JobType")
     * @ORM\JoinColumn(name="new_job_type", referencedColumnName="id")
     */
    private $newJobType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JobType")
     * @ORM\JoinColumn(name="old_job_type", referencedColumnName="id")
     */
    private $oldJobType;


    public function getResponsibleUser(): ?User
    {
        return $this->responsibleUser;
    }

    public function setResponsibleUser(?User $responsibleUser): self
    {
        $this->responsibleUser = $responsibleUser;
        return $this;
    }
}