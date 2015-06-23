=== miniOrange WP LDAP Login ===
Contributors: miniOrange
Donate link: http://miniorange.com
Tags:ldap, AD,ldap gateway, ldap sso, AD sso, ldap authentication, AD authentication, active directory authentication, ldap single sign on, ad single sign on, active directory single sign on, active directory, openldap, login form, user login, authentication, login, social login, WordPress login, widget, register, social user registration, user registration, open source single sign on for WordPress, sso saml, sso integration WordPress, sso, single sign on, two factor authentication, openldap sso, openldap single sign on, open ldap sso, open ldap single sign on
Requires at least: 2.0.2
Tested up to: 4.2.1
Stable tag: 2.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sign On to Wordpress with your LDAP Credentials using miniOrange Gateway

== Description ==

miniOrange WP LDAP Login plugin provides logon to WordPress using credentials stored in LDAP.

= Features :- =

= Credentials =
Users can login into WordPress site using credentials which are stored on your LDAP Server.

= Easy and Secure =
miniOrange WP LDAP Login plugin is easy to configure with the miniOrange Gateway. Secure access to your users credentials stored in LDAP Server. miniOrange WP LDAP Login performs encrypted authentication to your LDAP Server through miniOrange Gateway.

= Flexible =
miniOrange WP LDAP Login supports different LDAP implementations like Active Directory, OpenLDAP etc.

For more details - Refer: http://miniorange.com

== Installation ==

= Setting up miniOrange Customer account =
1. Go to <a href="https://auth.miniorange.com/moas/login" target="_blank">miniOrange login</a> . Register a new account by clicking on `Sign up for a Free Trial`.
2. Go back to <a href="https://auth.miniorange.com/moas/login" target="_blank">miniOrange login</a> and login with your credentials.
3. Go to `Users/Groups-> Manage Users/Groups-> Add User` and add users.

= Configuring miniOrange Gateway =
1. Install miniOrange Gateway on a DMZ server which has access to the internal LDAP server.
2. Once you are logged into the Admin Console of the Gateway, enter the LDAP Server URL, Service Account DN and Service Account Password. Click on Test Connection and Save.
3. If connection is successful, you will be shown a User-Mapping screen. Enter the Distinguished Name Attribute(eg. distinguishedName), LDAP Search Base and LDAP Search filter for your LDAP implementation.
4. Click on Save.

= Plugin installation =
1. Upload `miniorange-wpldaplogin.zip` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the `Plugins` menu in WordPress.
3. Go to `Settings-> LDAP Login Config`, and follow the instructions. Copy-paste the `Customer ID`, `API Key` and `Token Key` here.
4. Click on `Save`

== Frequently Asked Questions ==

= For any kind of problem =

Please email us at info@miniorange.com or <a href="http://miniorange.com/contact" target="_blank">Contact us</a>.

== Screenshots ==

1. Configure miniOrange Gateway
2. Configure application for Single Sign On

== Changelog ==

= 1.0.0 =
* this is the first release.

== Upgrade Notice ==

= 1.0 =
First version of plugin.
