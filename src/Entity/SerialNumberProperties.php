<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="serial_number_properties")
 */
class SerialNumberProperties {


    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private string $serialNumber = '';

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $notes = null;


    // Getters
    public function getId(): ?int {
        return $this->id;
    }


    public function getSerialNumber(): ?string {
        return $this->serialNumber;
    }

    public function setSerialNumber(?string $serialNumber): self {
        $this->serialNumber = $serialNumber;
        return $this;
    }

    public function getNotes(): ?string {
        return $this->notes;
    }

    public function setNotes(?string $notes): void {
        $this->notes = $notes;
    }


}