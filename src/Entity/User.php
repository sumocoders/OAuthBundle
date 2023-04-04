<?php

namespace SumoCoders\OAuthBundle\Entity;

use SumoCoders\OAuthBundle\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'user_external_id_origin', columns: ['external_id', 'origin'])]
#[ORM\Table(name: '`user`')]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private string $name;

    #[ORM\Column(length: 180)]
    private string $externalId;

    #[ORM\Column(length: 180)]
    private string $origin;

    #[ORM\Column]
    private array $roles = [];

    public function __construct(
        string $name,
        string $externalId,
        string $origin,
        array $roles
    ) {
        $this->name = $name;
        $this->externalId = $externalId;
        $this->origin = $origin;
        $this->roles = $roles;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExternalId(): string
    {
        return $this->externalId;
    }

    public function getOrigin(): string
    {
        return $this->origin;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function eraseCredentials()
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->externalId;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }
}
