=== miniOrange LDAP Login ===
Contributors: miniOrange
Donate link: http://miniorange.com
Tags:ldap, AD, ldap login, ldap sso, AD sso, ldap authentication, AD authentication, active directory authentication, ldap single sign on, ad single sign on, active directory single sign on, active directory, openldap login, login form, user login, authentication, login, WordPress login
Requires at least: 2.0.2
Tested up to: 4.2.1
Stable tag: 2.2.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Login to WordPress using credentials stored in your LDAP, LDAPS

== Description ==

miniOrange LDAP Login provides login to WordPress using credentials stored in your LDAP Server. It allows users to authenticate against various LDAP implementations like Microsoft Active Directory, OpenLDAP and other directory systems.

= Features :- =

*	Login to WordPress using your LDAP credentials
*	Uses LDAP or LDAPS for secure connection to your LDAP Server
*	Can authenticate users against multiple search bases
*	Test connection to your LDAP server
*	Test authentication using credentials stored in your LDAP server
*	Ability to test against demo LDAP server and demo credentials

= Do you want support? =
Please email us at info@miniorange.com or <a href="http://miniorange.com/contact" target="_blank">Contact us</a>

== Installation ==

= From your WordPress dashboard =
1. Visit `Plugins > Add New`
2. Search for `miniOrange LDAP Login`. Find and Install `miniOrange LDAP Login`
3. Activate the plugin from your Plugins page

= From WordPress.org =
1. Download miniOrange LDAP Login.
2. Unzip and upload the `miniorange-wp-ldap-login` directory to your `/wp-content/plugins/` directory.
3. Activate miniOrange LDAP Login from your Plugins page.

= Once Activated =
1. Upload `miniorange-wp-ldap-login.zip` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the `Plugins` menu in WordPress.
3. Go to `Settings-> LDAP Login Config`, and follow the instructions.
4. Click on `Save`

Make sure that if there is a firewall, you `OPEN THE FIREWALL` to allow incoming requests to your LDAP. Please open port 389(636 for SSL or ldaps). Host - 52.6.168.155 , 52.6.204.243 - This is the host from where the LDAP connection as well as authentication requests are going to be made.

== Frequently Asked Questions ==

= How should I enter my LDAP configuration? I only see Register with miniOrange. =
Our very simple and easy registration lets you register with miniOrange. miniOrange LDAP login works if you are connected to miniOrange. Once you have registered with a valid email-address and phone number, you will be able to add your LDAP configuration.

= I am not able to get the configuration right. =
Make sure that if there is a firewall, you `OPEN THE FIREWALL` to allow incoming requests to your LDAP. Please open port 389(636 for SSL or ldaps). Host - 52.6.168.155 , 52.6.204.243 - This is the host from where the LDAP connection as well as authentication requests are going to be made. For further help please click on the Troubleshooting button. Check the steps to see what you could have missed. If that does not help, please check the format of demo settings. You can copy them over using `Copy Default Config`.

= I am locked out of my account and can't login with either my WordPress credentials or LDAP credentials. What should I do? =
Firstly, please check if the `user you are trying to login with` exists in your WordPress. To unlock yourself, rename miniorange-wp-ldap-login plugin name. You will be able to login with your WordPress credentials. After logging in, rename the plugin back to miniorange-wp-ldap-login. If the problem persists, `activate, deactivate and again activate` the plugin.


= For support or troubleshooting help =
Please email us at info@miniorange.com or <a href="http://miniorange.com/contact" target="_blank">Contact us</a>.

== Screenshots ==

1. Configure LDAP plugin
2. Test demo LDAP plugin

== Changelog ==

= 2.0.2 =
Usability fixes

= 2.0.1 =
Bug fix

= 2.0.0 =
LDAP usability fixes

= 1.0.0 =
* this is the first release.

== Upgrade Notice ==

= 2.0.2 =
Usability fixes

= 2.0.1 =
Bug fix

= 2.0.0 =
LDAP usability fixes

= 1.0 =
First version of plugin.
