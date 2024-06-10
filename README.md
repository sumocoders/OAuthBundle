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
