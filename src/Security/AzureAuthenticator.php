<?php

namespace SumoCoders\OAuthBundle\Security;

use Psr\Log\LoggerInterface;
use SumoCoders\OAuthBundle\Entity\User;
use SumoCoders\OAuthBundle\Event\LoginEvent;
use SumoCoders\OAuthBundle\Repository\UserRepository;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TheNetworg\OAuth2\Client\Provider\AzureResourceOwner;

class AzureAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    const ORIGIN = 'azure';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        private readonly ClientRegistry $clientRegistry,
        private readonly UserRepository $userRepository,
        private readonly RouterInterface $router,
        private readonly string $successRoute = 'home',
        private readonly string $failureRoute = 'home',
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_azure_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('azure');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var AzureResourceOwner $azureUser */
                $azureUser = $client->fetchUserFromToken($accessToken);

                $roles = $azureUser->claim('roles');

                if ($roles === null) {
                    $roles = [];
                }

                $existingUser = $this->userRepository->findOneBy([
                    'externalId' => $azureUser->getId(),
                    'origin' => self::ORIGIN,
                ]);

                if ($existingUser) {
                    $existingUser->setRoles($roles);
                    $this->userRepository->save($existingUser, true);

                    $this->eventDispatcher->dispatch(
                        new LoginEvent($existingUser, self::ORIGIN)
                    );

                    return $existingUser;
                }

                $user = new User(
                    $azureUser->claim('preferred_username'),
                    $azureUser->getId(),
                    self::ORIGIN,
                    $roles
                );

                $this->userRepository->save($user, true);

                $this->eventDispatcher->dispatch(
                    new LoginEvent($user, self::ORIGIN)
                );

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $this->logger->info('User successfully authenticated', ['user' => $token->getUser()]);

        return new RedirectResponse(
            $this->router->generate($this->successRoute)
        );
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->logger->error($exception->getMessage(), ['exception' => $exception]);

        $this->requestStack->getSession()->getFlashBag()->add(
            'error',
            $this->translator->trans('login.error', [], 'azure')
        );

        return new RedirectResponse(
            $this->router->generate($this->failureRoute)
        );
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            $this->router->generate('connect_azure_start'),
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
