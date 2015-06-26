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
2. You will receive an email. Go back to <a href="https://auth.miniorange.com/moas/login" target="_blank">miniOrange login</a> and login with your credentials.
3. Go to `Users/Groups-> Manage Users/Groups-> Add User` and add users.

= Configuring miniOrange Gateway =
1. LDAP Connection String -> Connection string for the LDAP Server. eg: ldap://myldapserver.domain:port
2. Service Account Distinguished Name(DN). eg: cn=admin,dc=domain,dc=com
3. Server Account Password
4. DistinguishedName Attribute (DN Attribute) -> attribute in LDAP which stores unique DN value. eg: distinguishedName in AD, entryDN in OpenLDAP
5. SearchBase -> Define where users logging in will be located in the LDAP Environment
6. Search Filter -> It is a basic LDAP Query for searching of user based on mapping of username to a particular attribute. eg: (&(objectClass=*)(cn=?))

Please email us at info@miniorange.com for configuration of miniOrange Gateway. Also send us the above information so we can help you set it up.

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
