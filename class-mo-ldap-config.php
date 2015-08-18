<?php
/** miniOrange enables user to log in using LDAP credentials.
    Copyright (C) 2015  miniOrange

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
* @package 		miniOrange OAuth
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/
/**
This library is miniOrange Authentication Service. 
Contains Request Calls to LDAP Service.

**/
class Mo_Ldap_Config{
	
	function ldap_login($username, $password) {
		if(!Mo_Ldap_Util::is_curl_installed()) {
			return 'CURL_ERROR';
		}else if(!Mo_Ldap_Util::is_extension_installed('mcrypt')) {
			return 'MCRYPT_ERROR';
		}
		
		$url = get_option('mo_ldap_host_name')  . "/moas/api/ldap/authenticate";
		global $post;
		//Send cURL request to gateway url and parse response
		
		//calls to encrypt username and password
		$encrypted_username = Mo_Ldap_Util::encrypt($username);
		$encrypted_password = Mo_Ldap_Util::encrypt($password);
		
		//send post request to gateway url
		$data = $this->get_login_config($encrypted_username, $username, $encrypted_password, 'User Login through LDAP', null);
		$data_string = json_encode($data);
		$curl = curl_init();
		
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $url,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $data_string,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HTTPHEADER => array(                                                                          
				'Content-Type: application/json',                                                                                
				'Content-Length: ' . strlen($data_string))
		));
		$response = curl_exec($curl);
		
		if (curl_errno($curl)) {
			   print curl_error($curl);
			   exit(0);
		} 
		$decoded_response = (array)json_decode($response);

		curl_close($curl);
		
		$status = $decoded_response['statusCode'];
		return $status;
	}
	
	function save_ldap_config(){
		if(!Mo_Ldap_Util::is_curl_installed()) {
			return json_encode(array("statusCode"=>'CURL_ERROR','statusMessage'=>'<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
		}
		
		$url = get_option('mo_ldap_host_name') . '/moas/api/ldap/update-config';
		$ch = curl_init($url);
		
		$fields = $this->get_encrypted_config('Save LDAP Configuration', null);
		$field_string = json_encode($fields);
		
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 20);
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'charset: UTF - 8',
			'Authorization: Basic'
		));
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
		$content = curl_exec($ch);
		
		if(curl_errno($ch)){
			$error = curl_error($ch);
			if(curl_errno($ch) == 28) {
				$error = 'Connection request timed out';
			}
			curl_close($ch);
			return json_encode(array("statusCode"=>'ERROR','statusMessage'=>$error . '. Please check your configuration. Also check troubleshooting under LDAP configuration.'));
		} else {
			curl_close($ch);
			return $content;
		}
	}
	
	/*
	*	Test connection for default config or user config
	*/
	function test_connection($is_default) {
		if(!Mo_Ldap_Util::is_curl_installed()) {
			return json_encode(array("statusCode"=>'CURL_ERROR','statusMessage'=>'<a target="_blank" href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
		}
		
		$url = '';
		$request_type = '';
		
		//Check if request is for default connection
		if(Mo_Ldap_Util::check_empty_or_null($is_default)) {
			$url = get_option('mo_ldap_host_name') . "/moas/api/ldap/test";
			$request_type = 'Test User Connection';
		} else {
			$url = get_option('mo_ldap_host_name') . "/moas/api/ldap/test-default";
			$request_type = 'Test Default Connection';
		}
		
		$ch = curl_init($url);
		
		$fields = $this->get_encrypted_config($request_type, $is_default);
		$field_string = json_encode($fields);
		
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 20);
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'charset: UTF - 8',
			'Authorization: Basic'
			));
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
		$content = curl_exec($ch);
		
		if(curl_errno($ch)){
			$error = curl_error($ch);
			if(curl_errno($ch) == 28) {
				$error = 'Connection request timed out';
			}
			curl_close($ch);
			return json_encode(array("statusCode"=>'ERROR','statusMessage'=>$error . '. Please check your configuration. Also check troubleshooting under LDAP configuration.'));
		} else {
			curl_close($ch);
			return $content;
		}
	}
	
	/*
	*	Test authentication for default config or user config
	*/
	function test_authentication($username, $password, $is_default) {
		if(!Mo_Ldap_Util::is_curl_installed()) {
			return json_encode(array("statusCode"=>'CURL_ERROR','statusMessage'=>'<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
		}else if(!Mo_Ldap_Util::is_extension_installed('mcrypt')) {
			return json_encode(array("statusCode"=>'MCRYPT_ERROR','statusMessage'=>'<a href="http://php.net/manual/en/mcrypt.installation.php">PHP mcrypt extension</a> is not installed or disabled.'));
		}
		
		$url = '';
		$request_type = '';
		
		//Check if request is for default auth
		if(Mo_Ldap_Util::check_empty_or_null($is_default)) {
			$url = get_option('mo_ldap_host_name') . "/moas/api/ldap/authenticate";
			$request_type = 'Test User Login';
			$encrypted_username = Mo_Ldap_Util::encrypt($username);
			$encrypted_password = Mo_Ldap_Util::encrypt($password);
		} else {
			$url = get_option('mo_ldap_host_name') . "/moas/api/ldap/authenticate-default";
			$request_type = 'Test Default Login';
			$encrypted_username = $username;
			$encrypted_password = $password;
		}
		
		$ch = curl_init($url);
		
		$fields = $this->get_login_config($encrypted_username, $username, $encrypted_password, $request_type, $is_default);
		$field_string = json_encode($fields);
		
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt( $ch, CURLOPT_TIMEOUT, 20);
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'charset: UTF - 8',
			'Authorization: Basic'
			));
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
		$content = curl_exec($ch);
		
		if(curl_errno($ch)){
			$error = curl_error($ch);
			if(curl_errno($ch) == 28) {
				$error = 'Connection request timed out';
			}
			curl_close($ch);
			return json_encode(array("statusCode"=>'ERROR','statusMessage'=>$error . '. Please check your configuration. Also check troubleshooting under LDAP configuration.'));
		} else {
			curl_close($ch);
			return $content;
		}
	}
	
	function get_encrypted_config($request_type, $is_default) {
		global $current_user;
		get_currentuserinfo();
		
		$server_name = '';
		$dn = '';
		$admin_ldap_password = '';
		$dn_attribute = '';
		$search_base = '';
		$search_filter = '';
		$username = $current_user->user_email;
		
		if(Mo_Ldap_Util::check_empty_or_null($is_default)) {
			$server_name = get_option( 'mo_ldap_server_url');
			$dn = get_option( 'mo_ldap_server_dn');
			$admin_ldap_password = get_option( 'mo_ldap_server_password');
			$dn_attribute = get_option( 'mo_ldap_dn_attribute');
			$search_base = get_option( 'mo_ldap_search_base');
			$search_filter = get_option( 'mo_ldap_search_filter');
			$username = get_option('mo_ldap_admin_email');
		}
		$customer_id = get_option('mo_ldap_admin_customer_key') ? get_option('mo_ldap_admin_customer_key') : null;
		
		$fields = array(
			'customerId' => $customer_id,
			'ldapAuditRequest' => array(
				'endUserEmail' => $username,
				'applicationName' => $_SERVER['SERVER_NAME'],
				'appType' => 'WP LDAP Login Plugin',
				'requestType' => $request_type
			),
			'gatewayConfiguration' => array(
				'ldapServer' =>$server_name,
				'bindAccountDN'=>$dn,
				'bindAccountPassword'=>$admin_ldap_password,
				'searchBase'=>$search_base,
				'dnAttribute'=>$dn_attribute,
				'ldapSearchFilter'=>$search_filter
			)
		);
		
		return $fields;
	}
	
	function get_login_config($encrypted_username, $username, $encrypted_password, $request_type, $is_default) {
		global $current_user;
		get_currentuserinfo();
		
		$customer_id = get_option('mo_ldap_admin_customer_key') ? get_option('mo_ldap_admin_customer_key') : null;
		
		$fields = array(
			'customerId' => $customer_id,
			'userName' => $encrypted_username,
			'password' => $encrypted_password,
			'ldapAuditRequest' => array(
				'endUserEmail' => $username,
				'applicationName' => $_SERVER['SERVER_NAME'],
				'appType' => 'WP LDAP Login Plugin',
				'requestType' => $request_type
			)
		);
		
		return $fields;
	}
}?>