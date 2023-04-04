## Create an application in Azure

When setting up the application a callback url is required. For an Azure application this is the following url: `/connect/azure/check`

While creating the app you will need to define all roles that are present in the application. See [Configure the roles](#configure-the-roles).

## Configure the roles
* Go to the [Azure Portal](https://portal.azure.com/#allservices/category/All)
* Search for "Azure Active Directory"
* Click "App registrations" on the lefthand side
* Select your created application
* Click "App roles" on the left.
* Create a role for each role in your application
* The field value should match the role defined in your application

Full article: [Add app roles to your application and receive them in the token](https://learn.microsoft.com/en-us/azure/active-directory/develop/howto-add-app-roles-in-azure-ad-apps)


## Give users a role
* Go to the [Azure Portal](https://portal.azure.com/#allservices/category/All)
* Search for "Azure Active Directory"
* Click "Enterprise applications" on the lef
* Select your created application
* Select "Users and groups" on the left.
* Add user/groups with the correct role

Full article: [Assign users and groups to roles](https://learn.microsoft.com/en-us/azure/active-directory/develop/howto-add-app-roles-in-azure-ad-apps#assign-users-and-groups-to-roles)

## Configure the application
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
    enable_authenticator_manager: true
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
            custom_authenticators:
                - SumoCoders\OAuthBundle\Security\AzureAuthenticator
            logout:
                path: logout
                target: home #Your home page
```
Add the following ENV variables to your .env file

```dotenv
AZURE_CLIENT_ID= #Your client id
AZURE_CLIENT_SECRET= #Your client secret
AZURE_TENANT_ID= #Your tenant id
```

## Configure the routes
Add the following routes to your routes.yaml file

```yaml
oauth_bundle:
    resource: '@SumoCodersOAuthBundle/config/routes.yaml'
    prefix: /
```
