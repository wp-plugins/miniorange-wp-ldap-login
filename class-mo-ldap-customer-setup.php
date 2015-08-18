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
Contains Request Calls to Customer service.

**/
class Mo_Ldap_Customer{
	
	public $email;
	public $phone;
	public $customerKey;
	public $transactionId;
	private $defaultCustomerKey = "16555";
	private $defaultApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
	
	function create_customer(){
		if(!Mo_Ldap_Util::is_curl_installed()) {
			return json_encode(array("statusCode"=>'ERROR','statusMessage'=>$error . '. Please check your configuration. Also check troubleshooting under LDAP configuration.'));
		}
		$url = get_option('mo_ldap_host_name') . '/moas/rest/customer/add';
		$ch = curl_init($url);
		global $current_user;
		get_currentuserinfo();
		$this->email = get_option('mo_ldap_admin_email');
		$this->phone = get_option('mo_ldap_admin_phone');
		$password = get_option('mo_ldap_password');
		
		$fields = array(
			'companyName' => $_SERVER['SERVER_NAME'],
			'areaOfInterest' => 'WP LDAP Plugin',
			'firstname' => $current_user->user_firstname,
			'lastname' => $current_user->user_lastname,
			'email' => $this->email,
			'phone' => $this->phone,
			'password' => $password
		);
		$field_string = json_encode($fields);
		
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
		
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
			echo 'Request Error:' . curl_error($ch);
		   exit();
		}
		

		curl_close($ch);
		return $content;
	}
	
	function get_customer_key() {
		if(!Mo_Ldap_Util::is_curl_installed()) {
			return json_encode(array("apiKey"=>'CURL_ERROR','token'=>'<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
		}
		
		$url = get_option('mo_ldap_host_name') . "/moas/rest/customer/key";
		$ch = curl_init($url);
		$email = get_option('mo_ldap_admin_email');
		$password = get_option('mo_ldap_password');
		
		$fields = array(
			'email' => $email,
			'password' => $password
		);
		$field_string = json_encode($fields);
		
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
		
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
			echo 'Request Error:' . curl_error($ch);
		   exit();
		}
		curl_close($ch);

		return $content;
	}
	
	function submit_contact_us( $q_email, $q_phone, $query ) {
		if(!Mo_Ldap_Util::is_curl_installed()) {
			return json_encode(array("status"=>'CURL_ERROR','statusMessage'=>'<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
		}
		
		$url = get_option('mo_ldap_host_name') . "/moas/rest/customer/contact-us";
		$ch = curl_init($url);
		global $current_user;
		get_currentuserinfo();
		$query = '[miniOrange LDAP Login]: ' . $query;
		$fields = array(
			'firstName'			=> $current_user->user_firstname,
			'lastName'	 		=> $current_user->user_lastname,
			'company' 			=> $_SERVER['SERVER_NAME'],
			'email' 			=> $q_email,
			'phone'				=> $q_phone,
			'query'				=> $query
		);
		$field_string = json_encode( $fields );
		
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls
		
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'charset: UTF-8', 'Authorization: Basic' ) );
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
		$content = curl_exec( $ch );
		
		if(curl_errno($ch)){
			echo 'Request Error:' . curl_error($ch);
		   return false;
		}
		curl_close($ch);

		return true;
	}
	
	function send_otp_token(){
		if(!Mo_Ldap_Util::is_curl_installed()) {
			return json_encode(array("status"=>'CURL_ERROR','statusMessage'=>'<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
		}
		
		$url = get_option('mo_ldap_host_name') . '/moas/api/auth/challenge';
		$ch = curl_init($url);
		$customerKey =  $this->defaultCustomerKey;
		$apiKey =  $this->defaultApiKey;

		$username = get_option('mo_ldap_admin_email');

		/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
		$currentTimeInMillis = round(microtime(true) * 1000);

		/* Creating the Hash using SHA-512 algorithm */
		$stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
		$hashValue = hash("sha512", $stringToHash);

		$customerKeyHeader = "Customer-Key: " . $customerKey;
		$timestampHeader = "Timestamp: " . $currentTimeInMillis;
		$authorizationHeader = "Authorization: " . $hashValue;

		$fields = array(
			'customerKey' => $this->defaultCustomerKey,
			'email' => $username,
			'authType' => 'EMAIL',
		);
		$field_string = json_encode($fields);

		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
											$timestampHeader, $authorizationHeader));
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
		$content = curl_exec($ch);

		if(curl_errno($ch)){
			echo 'Request Error:' . curl_error($ch);
		   exit();
		}
		curl_close($ch);
		return $content;
	}

	function validate_otp_token($transactionId,$otpToken){
		if(!Mo_Ldap_Util::is_curl_installed()) {
			return json_encode(array("status"=>'CURL_ERROR','statusMessage'=>'<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
		}
		
		$url = get_option('mo_ldap_host_name') . '/moas/api/auth/validate';
		$ch = curl_init($url);

		$customerKey =  $this->defaultCustomerKey;
		$apiKey =  $this->defaultApiKey;

		$username = get_option('mo_ldap_admin_email');

		/* Current time in milliseconds since midnight, January 1, 1970 UTC. */
		$currentTimeInMillis = round(microtime(true) * 1000);

		/* Creating the Hash using SHA-512 algorithm */
		$stringToHash = $customerKey . $currentTimeInMillis . $apiKey;
		$hashValue = hash("sha512", $stringToHash);

		$customerKeyHeader = "Customer-Key: " . $customerKey;
		$timestampHeader = "Timestamp: " . $currentTimeInMillis;
		$authorizationHeader = "Authorization: " . $hashValue;

		$fields = '';

			//*check for otp over sms/email
			$fields = array(
				'txId' => $transactionId,
				'token' => $otpToken,
			);

		$field_string = json_encode($fields);

		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
											$timestampHeader, $authorizationHeader));
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
		$content = curl_exec($ch);

		if(curl_errno($ch)){
			echo 'Request Error:' . curl_error($ch);
		   exit();
		}
		curl_close($ch);
		return $content;
	}
	
	function check_customer() {
		if(!Mo_Ldap_Util::is_curl_installed()) {
			return json_encode(array("status"=>'CURL_ERROR','statusMessage'=>'<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
		}
		
		$url 	= get_option('mo_ldap_host_name') . "/moas/rest/customer/check-if-exists";
		$ch 	= curl_init( $url );
		$email 	= get_option("mo_ldap_admin_email");

		$fields = array(
			'email' 	=> $email,
		);
		$field_string = json_encode( $fields );

		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );    # required for https urls

		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'charset: UTF - 8', 'Authorization: Basic' ) );
		curl_setopt( $ch, CURLOPT_POST, true);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $field_string);
		$content = curl_exec( $ch );
		if( curl_errno( $ch ) ){
			echo 'Request Error:' . curl_error( $ch );
			exit();
		}
		curl_close( $ch );

		return $content;
	}
	
	function ping_ldap_server($ldap_server_url){
		if(!Mo_Ldap_Util::is_curl_installed()) {
			return json_encode(array("statusCode"=>'CURL_ERROR','statusMessage'=>'<a target="_blank" href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
		}
		
		$customer_id = get_option('mo_ldap_admin_customer_key');
		$application_name = $_SERVER['SERVER_NAME'];
		$admin_email = get_option('mo_ldap_admin_email');
		$app_type = 'WP LDAP Login Plugin';
		$request_type = 'Ping LDAP Server';
		$url = get_option('mo_ldap_host_name') . '/moas/api/ldap/ping?customerId=' . urlencode($customer_id)
				. '&endUserEmail=' . urlencode($admin_email) . '&ldapServerUrl=' . urlencode($ldap_server_url)
				. '&appType=' . urlencode($app_type) . '&requestType=' . urlencode($request_type) . '&applicationName=' . urlencode($application_name);
		
		$ch 	= curl_init( $url );
		
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_ENCODING, "" );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_AUTOREFERER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		
		curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'charset: UTF - 8', 'Authorization: Basic' ) );
		curl_setopt( $ch, CURLOPT_POST, false);
		$content = curl_exec( $ch );
		
		if( curl_errno( $ch ) ){
			echo 'Request Error:' . curl_error( $ch );
			exit();
		}
		curl_close( $ch );
		
		return $content;
		
	}


}?>