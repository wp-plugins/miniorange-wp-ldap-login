=== miniOrange WP LDAP Login ===
Contributors: miniOrange
Donate link: http://miniorange.com
Tags:ldap, AD, miniorange ldap gateway, ldap sso, AD sso, ldap authentication, AD authentication, active directory authentication, ldap single sign on, ad single sign on, active directory single sign on, active directory, openldap, miniorange login form, mo user login, miniorange authentication, miniOrange, mo, login form, miniorange login, social login, WordPress login, widget, miniorange register, social user registration, user registration, open source single sign on for WordPress, sso saml, sso integration WordPress
Requires at least: 2.0.2
Tested up to: 4.2.1
Stable tag: 2.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sign On to Wordpress with LDAP Credentials using miniOrange Gateway

== Description ==

miniOrange WP LDAP Login Plugin provides logon to Wordpress using credentials stored in LDAP. Connection is made to the miniOrange Gateway which has access to the LDAP Server. 

= Features :- =

= Credentials =
Users can login into Wordpress site using credentials which are stored on an LDAP Server.

= Security =
Plugin does not directly call the LDAP Server for authentication. Instead, call to the miniOrange Gateway is made which in turns communicates with the LDAP Server. Communication between miniOrange Gateway and the LDAP Server is encrypted.

= SSO into Cloud Apps =
Using miniOrange Single Sign On, users can SSO into various SAML enabled cloud applications, such as Dropbox, Google Apps etc.

= Diversity =
Support for different LDAP implementations like Active Directory, OpenLDAP etc.

For more details - Refer: http://miniorange.com

== Installation ==

= Setting up miniOrange Customer account =
1. Go to <a href="https://auth.miniorange.com/moas/login" target="_blank">miniOrange login</a> . Register a new account by clicking on `Sign up for a Free Trial`.
2. Go back to <a href="https://auth.miniorange.com/moas/login" target="_blank">miniOrange login</a> and login with your credentials.
3. Go to `Users/Groups-> Manage Users/Groups-> Add User` and add users.
4. Go to `Apps`. Then click on `Configure Apps` button.
5. Select `SAML` and click on `Add App`.
6. Enter your WordPress application name in `Client Name`. And in the `Redirect URL` add : `<your-WordPress-site-url>/?option=mologin`. Optionally add `Description`.
7. Click save. Go to `Edit` link beside Application Name. Note the `Client ID` and `Client Secret`.
8. Go to `Policies-> App Authentication Policy-> Add Policy`. 
9. Select your application name from the dropdown list. Select the group of your users, add a policy name and select your authentication type.

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
