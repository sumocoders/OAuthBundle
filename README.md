## Create an application in Azure

* Go to [Azure Portal]([https://portal.azure.com/#view/Microsoft_AAD_RegisteredApps/ApplicationsListBlade](https://portal.azure.com/#home))
* Search for "App registrations"
* Click "New registration"
  * Name: The name of the application, eg: the url of the webapplication
  * Supported account types: select "Accounts in this organizational directory only (... only - single tenant)"
  * Redirect URI:
    * platform: web, url: https://xxx/connect/azure/check
    * platform: web, url: https://xxx.phpXX.sumocoders.eu/connect/azure/check
    * platform: web, url: https://xxx.wip/connect/azure/check
* Click "Certificates & Secrets"
* Click "New client secret"
  * Description: the url of the webapplication
  * Expires: 12 months
* Note down:
  * Application (client) ID
  * Directory (tenant) ID
  * Client secret Value
  * Client secret ID

Full article: [Register a Microsoft Entra app and create a service principal](https://learn.microsoft.com/en-us/entra/identity-platform/howto-create-service-principal-portal)

## Allow the application to be used

When this is done, you still need to allow the users to use this application:

* Go to [Azure Portal]([https://portal.azure.com/#view/Microsoft_AAD_RegisteredApps/ApplicationsListBlade](https://portal.azure.com/#home))
* Search for "App registrations"
* Select the newly created application
* Select "Security → Permisions" on the left
* Click "Granty admin consent for ..."

Full article: [Grant tenant-wide admin consent to an application](https://learn.microsoft.com/en-us/azure/active-directory/manage-apps/grant-admin-consent?pivots=portal)

## Configure the roles
* Go to the [Azure Portal]([https://portal.azure.com/#allservices/category/All](https://portal.azure.com/#home))
* Search for "App registrations"
* Select your created application
* Click "Manage → App roles" on the left.
* Create a role for each role in your application
* The field value should match the role defined in your application

Full article: [Add app roles to your application and receive them in the token](https://learn.microsoft.com/en-us/azure/active-directory/develop/howto-add-app-roles-in-azure-ad-apps)


## Give users a role
* Go to the [Azure Portal](https://portal.azure.com/#allservices/category/All)
* Search for "Microsoft Entra ID"
* Click "Enterprise applications" on the left
* Select your created application
* Select "Users and groups" on the left.
* Add user/groups with the correct role

Full article: [Assign users and groups to roles](https://learn.microsoft.com/en-us/azure/active-directory/develop/howto-add-app-roles-in-azure-ad-apps#assign-users-and-groups-to-roles)

## Configure the application
This example shows two applications, the default 'azure' and 'sumocoders'.

Add the needed bundles to your bundles.php file

```php
return [
    ...,
    KnpU\OAuth2ClientBundle\KnpUOAuth2ClientBundle::class => ['all' => true],
    SumoCoders\OAuthBundle\SumoCodersOAuthBundle::class => ['all' => true],
];
```

Update your security.yml file to mirror the following config
    
```yaml
security:
    providers:
        app_user_provider:
            entity:
            class: SumoCoders\OAuthBundle\Entity\User
            property: externalId
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            lazy: true
            provider: app_user_provider
            entry_point: SumoCoders\OAuthBundle\Security\AzureAuthenticator
            custom_authenticators:
                - SumoCoders\OAuthBundle\Security\AzureAuthenticator
                - azure_authenticator_sumocoders
            logout:
                path: logout
                target: home #Your home page
```

Define the extra custom authenticators in services.yaml

The client parameter should be the same as defined in knpu_oauth2_client (see below)

```yaml
services:
    azure_authenticator_sumocoders:
        class: SumoCoders\OAuthBundle\Security\AzureAuthenticator
        arguments:
            $client: 'sumocoders'
```

Add the following ENV variables to your .env file

```dotenv
AZURE_CLIENT_ID= #Your client id
AZURE_CLIENT_SECRET= #Your client secret
AZURE_TENANT= #Your tenant id

SUMOCODERS_CLIENT_ID=
SUMOCODERS_CLIENT_SECRET=
SUMOCODERS_TENANT=
```

## Configure the routes
Add the following routes to your routes.yaml file

Make sure the prefix of the extra routes is the same as the client name.

```yaml
oauth_bundle:
    resource: '@SumoCodersOAuthBundle/config/routes.yaml'
    prefix: /

oauth_bundle_sumocoders:
    resource: '@SumoCodersOAuthBundle/config/routes.yaml'
    prefix: /sumocoders
    name_prefix: sumocoders_
```

## Configure the OAuth bundle
Add the following clients to your knpu_oauth2_client.yaml file

```yaml
knpu_oauth2_client:
    clients:
        azure:
            type: azure
            client_id: '%env(AZURE_CLIENT_ID)%'
            client_secret: '%env(AZURE_CLIENT_SECRET)%'
            redirect_route: connect_azure_check
            default_end_point_version: 2.0
            tenant: '%env(AZURE_TENANT)%'

        sumocoders:
            type: azure
            client_id: '%env(SUMOCODERS_CLIENT_ID)%'
            client_secret: '%env(SUMOCODERS_CLIENT_SECRET)%'
            redirect_route: sumocoders_connect_azure_check
            default_end_point_version: 2.0
            tenant: '%env(SUMOCODERS_TENANT)%'
```
