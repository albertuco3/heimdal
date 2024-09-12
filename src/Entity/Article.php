<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="article")
 */
class Article {

    public function __toString() {
        return $this->getCode() . " (" . $this->getSerialNumber() . ")";
    }

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $lineNumber = null;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $code = null;


    /**
     * @ORM\Column(type="boolean", nullable=true,options={"default": false})
     */
    private bool $parked = false;

    public function isParked(): bool {
        return $this->parked;
    }

    public function setParked(bool $parked): void {
        $this->parked = $parked;
    }

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $customer = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $serialNumber = null;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $orderNumber = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $deliveryNoteId = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $companyId = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $priority = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="ownedArticles")
     */
    private ?User $owner = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="receivedArticles")
     */
    private ?User $receiver = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JobType")
     */
    private ?JobType $jobType = null;

    // Getters
    public function getId(): ?int {
        return $this->id;
    }

    public function getCode(): ?string {
        return $this->code;
    }

    public function setCode(?string $code): self {
        $this->code = $code;
        return $this;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): self {
        $this->description = $description;
        return $this;
    }

    public function getSerialNumber(): ?string {
        return $this->serialNumber;
    }

    public function setSerialNumber(?string $serialNumber): self {
        $this->serialNumber = $serialNumber;
        return $this;
    }

    public function getDeliveryNoteId(): ?string {
        return $this->deliveryNoteId;
    }

// Setters

    public function setDeliveryNoteId(?string $deliveryNoteId): self {
        $this->deliveryNoteId = $deliveryNoteId;
        return $this;
    }

    public function getOwner(): ?User {
        return $this->owner;
    }

    public function setOwner(?User $owner): self {
        $this->owner = $owner;
        return $this;
    }

    public function getReceiver(): ?User {
        return $this->receiver;
    }

    public function setReceiver(?User $receiver): self {
        $this->receiver = $receiver;
        return $this;
    }

    public function getJobType(): ?JobType {
        return $this->jobType;
    }

    public function setJobType(?JobType $jobType): self {
        $this->jobType = $jobType;
        return $this;
    }

    public function getCustomer(): ?string {
        return $this->customer;
    }

    public function setCustomer(?string $customer): void {
        $this->customer = $customer;
    }

    public function getOrderNumber(): ?string {
        return $this->orderNumber;
    }

    public function setOrderNumber(?string $orderNumber): void {
        $this->orderNumber = $orderNumber;
    }

    public function getLineNumber(): ?int {
        return $this->lineNumber;
    }

    public function setLineNumber(?int $lineNumber): void {
        $this->lineNumber = $lineNumber;
    }

    public function getCompanyId(): ?string
    {
        return $this->companyId;
    }

    public function setCompanyId(?string $companyId): void
    {
        $this->companyId = $companyId;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(?int $priority): void
    {
        $this->priority = $priority;
    }

    public function getTranslatedPriority(): ?string
    {
        $priorityValue = $this->priority;
        
        $priorities = [
            [ "name" => 'Muy baja', "value" => -200 ],
            [ "name" => 'Baja', "value" => -100 ],
            [ "name" => 'Media', "value" => 0 ],
            [ "name" => 'Alta', "value" => 100 ],
            [ "name" => 'Muy alta', "value" => 200]
        ];

        foreach ($priorities as $priority) {
            if ($priority['value'] === $priorityValue) {
                return $priority['name'];
            }
        }

        return null;
    }



}