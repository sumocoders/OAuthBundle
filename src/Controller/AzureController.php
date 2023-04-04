<?php

namespace SumoCoders\OAuthBundle\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AzureController extends AbstractController
{
    #[Route('/connect/azure', name: 'connect_azure_start')]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('azure')
            ->redirect([
                    "openid",
                    "profile",
                    "email",
                    "offline_access",
                ]
            );
    }

    #[Route('/connect/azure/check', name: 'connect_azure_check')]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry)
    {
        // We handle the callback in the authenticator
    }
}
