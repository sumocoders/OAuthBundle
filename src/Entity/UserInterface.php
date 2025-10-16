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

    public function getName();

    public function getExternalId();

    public function getOrigin();

    /** @param string[] $roles */
    public function setRoles(array $roles);
}
