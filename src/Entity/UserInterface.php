<?php

namespace SumoCoders\OAuthBundle\Entity;

use SumoCoders\OAuthBundle\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

interface UserInterface extends SymfonyUserInterface
{
    /** @param string[] $roles */
    public static function fromAzure(
        string $name,
        string $externalId,
        string $origin,
        array $roles
    ): self;

    public function getName(): string;

    public function getExternalId(): string;

    public function getOrigin(): string;

    /** @param string[] $roles */
    public function setRoles(array $roles): void;
}
