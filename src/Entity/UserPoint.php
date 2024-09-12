<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="userpoint")
 */
class UserPoint {

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    public function getOwner(): ?User {
        return $this->owner;
    }

    public function setOwner(?User $owner): void {
        $this->owner = $owner;
    }

    public function getPoints(): ?float {
        return $this->points;
    }

    public function setPoints(float $points): void {
        $this->points = $points;
    }

    public function getDate(): ?\DateTimeInterface {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): void {
        $this->date = $date;
    }

    public function getReason(): ?string {
        return $this->reason;
    }

    public function setReason(?string $reason): void {
        $this->reason = $reason;
    }

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private ?User $owner = null;

    /**
     * @ORM\Column(type="float", nullable=false)
     */
    private ?float $points = null;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private ?\DateTimeInterface $date = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $reason = null;

    public function getId(): ?int {
        return $this->id;
    }

}