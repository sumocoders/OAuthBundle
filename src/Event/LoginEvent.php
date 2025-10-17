<?php

namespace SumoCoders\OAuthBundle\Event;

use SumoCoders\OAuthBundle\Entity\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class LoginEvent extends Event
{
    public const NAME = 'sumocoders.oauth.login';

    public function __construct(
        private readonly UserInterface $user,
        private readonly string $origin,
    ) {
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getOrigin(): string
    {
        return $this->origin;
    }
}
