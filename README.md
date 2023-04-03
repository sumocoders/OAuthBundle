## Create an application in Azure

While creating the app you will need to define all roles that are present in the application. See [Configure the roles](#configure-the-roles).

Full article: xxx

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
