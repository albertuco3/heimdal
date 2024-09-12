<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User implements UserInterface
{
    public function __toString()
    {
        return $this->getFullName();
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private string $username;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $first_name;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $last_name;

    /**
     * @ORM\Column(type="boolean", nullable=true,options={"default": false})
     */
    private bool $is_job_pool = false;


    // Implementación de los métodos de UserInterface
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // garantizar que siempre haya al menos un rol
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function isJobPool(): bool {
        return $this->is_job_pool;
    }

    public function setJobPool(bool $is_job_pool): void {
        $this->is_job_pool = $is_job_pool;
    }

    public function getSalt(): ?string
    {
        // No necesaria si utilizas bcrypt o argon2i
        return null;
    }

    public function eraseCredentials()
    {
        // Si almacenas información sensible temporalmente en el objeto, límpiala aquí
    }

    // Otros métodos que quieras implementar, como getters y setters para firstName, lastName, etc.
    public function getFirstName(): string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): void
    {
        $this->first_name = $first_name;
    }

    public function getLastName(): string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): void
    {
        $this->last_name = $last_name;
    }

    public function getFullName(): string
    {
        return $this->first_name . ($this->last_name ? " " . $this->last_name : "");
    }
}