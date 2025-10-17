<?php

namespace SumoCoders\OAuthBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Psr\Log\LoggerInterface;
use SumoCoders\OAuthBundle\Entity\User;
use SumoCoders\OAuthBundle\Entity\UserInterface;
use SumoCoders\OAuthBundle\Event\LoginEvent;
use SumoCoders\OAuthBundle\Repository\UserRepository;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
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
    public const ORIGIN = 'azure';

    /**
     * @param class-string<UserInterface> $userClass
     */
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
        private readonly ClientRegistry $clientRegistry,
        private readonly EntityManagerInterface $entityManager,
        private readonly RouterInterface $router,
        private readonly string $userClass = User::class,
        private readonly string $client = 'azure',
        private ?string $routePrefix = null,
        private readonly string $successRoute = 'home',
        private readonly string $failureRoute = 'home',
    ) {
        $this->routePrefix ??= ($this->client === 'azure' ? '' : $this->client . '_');
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === ($this->routePrefix . 'connect_azure_check');
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient($this->client);
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var AzureResourceOwner $azureUser */
                $azureUser = $client->fetchUserFromToken($accessToken);

                $roles = $azureUser->claim('roles');

                if ($roles === null) {
                    $roles = [];
                }

                /** @var ?UserInterface $existingUser */
                $existingUser = $this->entityManager->getRepository($this->userClass)->findOneBy([
                    'externalId' => $azureUser->getId(),
                    'origin' => self::ORIGIN,
                ]);

                if ($existingUser) {
                    $existingUser->setRoles($roles);
                    $this->entityManager->flush();

                    $this->eventDispatcher->dispatch(
                        new LoginEvent($existingUser, self::ORIGIN)
                    );

                    return $existingUser;
                }

                $user = $this->userClass::fromAzure(
                    $azureUser->claim('preferred_username'),
                    $azureUser->getId(),
                    self::ORIGIN,
                    $roles
                );

                $this->entityManager->persist($user);
                $this->entityManager->flush();

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

        $session = $this->requestStack->getSession();
        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add(
                'error',
                $this->translator->trans('login.error', [], 'azure')
            );
        }

        return new RedirectResponse(
            $this->router->generate($this->failureRoute)
        );
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            $this->router->generate($this->routePrefix . 'connect_azure_start'),
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
