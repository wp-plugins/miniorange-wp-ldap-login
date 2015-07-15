<?php 
    /*
    Plugin Name: miniOrange LDAP Login
    Plugin URI: http://miniorange.com
    Description: Plugin for login into Wordpress through credentials stored in LDAP
    Author: miniorange
    Version: 2.1.2
    Author URI: http://miniorange.com
    */
	
	require_once 'generate_saml_assertion.php';
	require_once 'mo_ldap_pages.php';
	require('mo_ldap_support.php');
	require('class-mo-ldap-customer-setup.php');
	require('class-mo-ldap-utility.php');
	require('class-mo-ldap-config.php');
	
	class Mo_Ldap_Login{
		
		function __construct(){
			add_action('admin_menu', array($this, 'mo_ldap_login_widget_menu'));
			add_action('admin_init', array($this, 'login_widget_save_options'));
			add_action( 'admin_enqueue_scripts', array( $this, 'mo_ldap_settings_style' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'mo_ldap_settings_script' ) );
			add_action('parse_request', array($this, 'parse_sso_request'));
			remove_action( 'admin_notices', array( $this, 'success_message') );
			remove_action( 'admin_notices', array( $this, 'error_message') );
			add_filter('query_vars', array($this, 'plugin_query_vars'));
			register_deactivation_hook(__FILE__, array( $this, 'mo_ldap_deactivate'));
			add_action( 'login_footer', 'mo_ldap_link' );
			if(get_option('mo_ldap_enable_login') == 1){
				remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
				add_filter('authenticate', array($this, 'ldap_login'), 20, 3);
			}
		}
		
		function ldap_login($user, $username, $password){			
			if(empty($username) || empty ($password)){        
				//create new error object and add errors to it.
				$error = new WP_Error();

				if(empty($username)){ //No email
					$error->add('empty_username', __('<strong>ERROR</strong>: Email field is empty.'));
				}
				
				if(empty($password)){ //No password
					$error->add('empty_password', __('<strong>ERROR</strong>: Password field is empty.'));
				}
				return $error;
			}
			
			$mo_ldap_config = new Mo_Ldap_Config();
			$status = $mo_ldap_config->ldap_login($username, $password);
			
			if($status == 'SUCCESS'){
			  if( username_exists( $username)) {
				  $user = get_userdatabylogin($username);
				  
				  return $user;
			   } else {
				  return $error;
			   }
				wp_redirect( site_url() );
				exit;
						
			} else if($status == 'CURL_ERROR'){
				$error = new WP_Error();
				$error->add('curl_error', __('<strong>ERROR</strong>: <a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
				return $error;
			} else {
				$error = new WP_Error();
				$error->add('incorrect_credentials', __('<strong>ERROR</strong>: Invalid username or incorrect password. Please try again.'));
				return $error;
			}
		}
	
		function mo_ldap_login_widget_menu(){
			add_options_page('LDAP Login Config', 'LDAP Login Config', 'activate_plugins', 'mo_ldap_login', array( $this, 'mo_ldap_login_widget_options'));
		}
		
		function mo_ldap_login_widget_options(){
			update_option( 'mo_ldap_host_name', 'https://auth.miniorange.com' );
			
			//Setting default configuration
			$default_config = array(
				'server_url' => 'ldap://58.64.132.235:389',
				'service_account_dn' => 'cn=testuser,cn=Users,dc=miniorange,dc=com',
				'admin_password' => 'XXXXXXXX',
				'dn_attribute' => 'distinguishedName',
				'search_base' => 'cn=Users,dc=miniorange,dc=com',
				'search_filter' => '(&(objectClass=*)(cn=?))',
				'test_username' => 'testuser',
				'test_password' => 'password'
			);
			update_option( 'mo_ldap_default_config', $default_config );
			mo_ldap_settings();
		}
		
		function login_widget_save_options(){
			if(isset($_POST['option'])){
				if($_POST['option'] == "mo_ldap_register_customer") {		//register the customer
				
					//validate and sanitize
					$email = '';
					$phone = '';
					$password = '';
					$confirmPassword = '';
					if( Mo_Ldap_Util::check_empty_or_null( $_POST['email'] ) || Mo_Ldap_Util::check_empty_or_null( $_POST['phone'] ) || Mo_Ldap_Util::check_empty_or_null( $_POST['password'] ) || Mo_Ldap_Util::check_empty_or_null( $_POST['confirmPassword'] ) ) {
						update_option( 'mo_ldap_message', 'All the fields are required. Please enter valid entries.');
						$this->show_error_message();
						return;
					} else if( strlen( $_POST['password'] ) < 6 || strlen( $_POST['confirmPassword'] ) < 6){	//check password is of minimum length 6
						update_option( 'mo_ldap_message', 'Choose a password with minimum length 6.');
						$this->show_error_message();
						return;
					} else{
						$email = sanitize_email( $_POST['email'] );
						$phone = sanitize_text_field( $_POST['phone'] );
						$password = sanitize_text_field( $_POST['password'] );
						$confirmPassword = sanitize_text_field( $_POST['confirmPassword'] );
					}
					update_option( 'mo_ldap_admin_email', $email );
					update_option( 'mo_ldap_admin_phone', $phone );
					
					if( strcmp( $password, $confirmPassword) == 0 ) {
						update_option( 'mo_ldap_password', $password );

						$customer = new Mo_Ldap_Customer();
						$content = json_decode($customer->check_customer(), true);
						if( strcasecmp( $content['status'], 'CUSTOMER_NOT_FOUND') == 0 ){
							$content = json_decode($customer->send_otp_token(), true);
							if(strcasecmp($content['status'], 'SUCCESS') == 0) {
								update_option( 'mo_ldap_message', ' A one time passcode is sent to ' . get_option('mo_ldap_admin_email') . '. Please enter the otp here to verify your email.');
								update_option('mo_ldap_transactionId',$content['txId']);
								update_option('mo_ldap_registration_status','MO_OTP_DELIVERED_SUCCESS');

								$this->show_success_message();
							} else {
								update_option('mo_ldap_message','There was an error in sending email. Please click on Resend OTP to try again.');
								update_option('mo_ldap_registration_status','MO_OTP_DELIVERED_FAILURE');
								$this->show_error_message();
							}
						} else if( strcasecmp( $content['status'], 'CURL_ERROR') == 0 ){
							update_option('mo_ldap_message', $content['statusMessage']);
							update_option('mo_ldap_registration_status','MO_OTP_DELIVERED_FAILURE');
							$this->show_error_message();
						} else{
							$content = $customer->get_customer_key();
							$customerKey = json_decode($content, true);
							if(json_last_error() == JSON_ERROR_NONE) {
								$this->save_success_customer_config($customerKey['id'], $customerKey['apiKey'], $customerKey['token'], 'Your account has been retrieved successfully.');
								update_option('mo_ldap_password', '');
							} else {
								update_option( 'mo_ldap_message', 'You already have an account with miniOrange. Please enter a valid password.');
								update_option('mo_ldap_verify_customer', 'true');
								delete_option('mo_ldap_new_registration');
								$this->show_error_message();
							}
						}

					} else {
						update_option( 'mo_ldap_message', 'Password and Confirm password do not match.');
						delete_option('mo_ldap_verify_customer');
						$this->show_error_message();
					}
				} 
				else if( $_POST['option'] == "mo_ldap_verify_customer" ) {	//login the admin to miniOrange
			
					//validation and sanitization
					$email = '';
					$password = '';
					if( Mo_Ldap_Util::check_empty_or_null( $_POST['email'] ) || Mo_Ldap_Util::check_empty_or_null( $_POST['password'] ) ) {
						update_option( 'mo_ldap_message', 'All the fields are required. Please enter valid entries.');
						$this->show_error_message();
						return;
					} else{
						$email = sanitize_email( $_POST['email'] );
						$password = sanitize_text_field( $_POST['password'] );
					}
				
					update_option( 'mo_ldap_admin_email', $email );
					update_option( 'mo_ldap_password', $password );
					$customer = new Mo_Ldap_Customer();
					$content = $customer->get_customer_key();
					$customerKey = json_decode( $content, true );
					if( strcasecmp( $customerKey['apiKey'], 'CURL_ERROR') == 0) {
						update_option('mo_ldap_message', $customerKey['token']);
						$this->show_error_message();
					} else if( json_last_error() == JSON_ERROR_NONE ) {
						update_option( 'mo_ldap_admin_phone', $customerKey['phone'] );
						$this->save_success_customer_config($customerKey['id'], $customerKey['apiKey'], $customerKey['token'], 'Your account has been retrieved successfully.');
						update_option('mo_ldap_password', '');
					} else {
						update_option( 'mo_ldap_message', 'Invalid username or password. Please try again.');
						$this->show_error_message();		
					}
					update_option('mo_ldap_password', '');
				}
				else if( $_POST['option'] == "mo_ldap_enable" ) {		//enable ldap login
					update_option( 'mo_ldap_enable_login', isset($_POST['enable_ldap_login']) ? $_POST['enable_ldap_login'] : 0);
					if(get_option('mo_ldap_enable_login')) {
						update_option( 'mo_ldap_message', 'Login through your LDAP has been enabled.');
						$this->show_success_message();
					} else {
						update_option( 'mo_ldap_message', 'Login through your LDAP has been disabled.');
						$this->show_success_message();
					}
				}
				else if( $_POST['option'] == "mo_ldap_save_config" ) {		//save ldap configuration
					
					//validation and sanitization
					$server_name = '';
					$dn = '';
					$admin_ldap_password = '';
					$dn_attribute = '';
					$search_base = '';
					$search_filter = '';
					if( Mo_Ldap_Util::check_empty_or_null( $_POST['ldap_server'] ) || Mo_Ldap_Util::check_empty_or_null( $_POST['dn'] ) || Mo_Ldap_Util::check_empty_or_null( $_POST['admin_password'] ) || Mo_Ldap_Util::check_empty_or_null( $_POST['dn_attribute'] ) || Mo_Ldap_Util::check_empty_or_null( $_POST['search_base'] ) || Mo_Ldap_Util::check_empty_or_null( $_POST['search_filter'] ) ) {
						update_option( 'mo_ldap_message', 'All the fields are required. Please enter valid entries.');
						$this->show_error_message();
						return;
					} else{
						$server_name = sanitize_text_field( $_POST['ldap_server'] );
						$dn = sanitize_text_field( $_POST['dn'] );
						$admin_ldap_password = sanitize_text_field( $_POST['admin_password'] );
						$dn_attribute = sanitize_text_field( $_POST['dn_attribute'] );
						$search_base = sanitize_text_field( $_POST['search_base'] );
						$search_filter = sanitize_text_field( $_POST['search_filter'] );
					}
					
					//Encrypting all fields and storing them
					update_option( 'mo_ldap_server_url', Mo_Ldap_Util::encrypt($server_name));
					update_option( 'mo_ldap_server_dn', Mo_Ldap_Util::encrypt($dn));
					update_option( 'mo_ldap_server_password', Mo_Ldap_Util::encrypt($admin_ldap_password));
					update_option( 'mo_ldap_dn_attribute', Mo_Ldap_Util::encrypt($dn_attribute));
					update_option( 'mo_ldap_search_base', Mo_Ldap_Util::encrypt($search_base));
					update_option( 'mo_ldap_search_filter', Mo_Ldap_Util::encrypt($search_filter));
					
					//This makes a call to check if connection is established successfully.
					$mo_ldap_config = new Mo_Ldap_Config();
					$content = $mo_ldap_config->test_connection(null);
					$response = json_decode( $content, true );
					
					if(strcasecmp($response['statusCode'], 'SUCCESS') == 0) {
						//This makes a call to save LDAP configuration
						$save_content = $mo_ldap_config->save_ldap_config();
						$save_response = json_decode( $save_content, true );
						
						if(strcasecmp($save_response['statusCode'], 'SUCCESS') == 0) {
							update_option( 'mo_ldap_message', 'Connection was established successfully. Your configuration has been saved. Please test authentication to verify LDAP User Mapping Configuration.');
							$this->show_success_message();
						} else if(strcasecmp($save_response['statusCode'], 'ERROR') == 0) {
							$this->delete_ldap_configuration();
							update_option( 'mo_ldap_message', 'Connection was established successfully but an error occured. ' . $save_response['statusMessage']);
							$this->show_error_message();
						} else {
							$this->delete_ldap_configuration();
							update_option( 'mo_ldap_message', 'Connection was established successfully but an error occured.');
							$this->show_error_message();
						}
					} else if(strcasecmp($response['statusCode'], 'ERROR') == 0) {
						$this->delete_ldap_configuration();
						update_option( 'mo_ldap_message', $response['statusMessage'] . ' Please make sure your firewall is open - click on troubleshooting to know more. Your configuration has not been saved.');
						$this->show_error_message();
					} else if( strcasecmp( $response['statusCode'], 'CURL_ERROR') == 0) {
						$this->delete_ldap_configuration();
						update_option('mo_ldap_message', $response['statusMessage']);
						$this->show_error_message();
					} else {
						$this->delete_ldap_configuration();
						update_option( 'mo_ldap_message', 'There was an error. Please make sure your firewall is open - click on troubleshooting to know more. Your configuration has not been saved.');
						$this->show_error_message();
					}
				}
				else if( $_POST['option'] == "mo_ldap_test_auth" ) {		//test authentication with current settings
					$server_name = get_option( 'mo_ldap_server_url');
					$dn = get_option( 'mo_ldap_server_dn');
					$admin_ldap_password = get_option( 'mo_ldap_server_password');
					$dn_attribute = get_option( 'mo_ldap_dn_attribute');
					$search_base = get_option( 'mo_ldap_search_base');
					$search_filter = get_option( 'mo_ldap_search_filter');
					
					//validation and sanitization
					$test_username = '';
					$test_password = '';
					
					//Check if username and password are empty
					if( Mo_Ldap_Util::check_empty_or_null( $_POST['test_username'] ) || Mo_Ldap_Util::check_empty_or_null( $_POST['test_password'] ) ) {
						update_option( 'mo_ldap_message', 'All the fields are required. Please enter valid entries.');
						$this->show_error_message();
						return;
					} 
					//Check if configuration is saved
					else if( Mo_Ldap_Util::check_empty_or_null( $server_name ) || Mo_Ldap_Util::check_empty_or_null( $dn ) || Mo_Ldap_Util::check_empty_or_null( 		$admin_ldap_password ) || Mo_Ldap_Util::check_empty_or_null( $dn_attribute ) || Mo_Ldap_Util::check_empty_or_null( $search_base ) || Mo_Ldap_Util::check_empty_or_null( $search_filter ) ) {
						update_option( 'mo_ldap_message', 'Please save LDAP Configuration to test authentication.');
						$this->show_error_message();
						return;
					} else{
						$test_username = sanitize_text_field( $_POST['test_username'] );
						$test_password = sanitize_text_field( $_POST['test_password'] );
					}
					
					//Call to authenticate test
					$mo_ldap_config = new Mo_Ldap_Config();
					$content = $mo_ldap_config->test_authentication($test_username, $test_password, null);
					$response = json_decode( $content, true );
					
					if(strcasecmp($response['statusCode'], 'SUCCESS') == 0) {
						update_option( 'mo_ldap_message', 'Test is successful! Your credentials have matched.');
						$this->show_success_message();
					} else if(strcasecmp($response['statusCode'], 'ERROR') == 0) {
						update_option( 'mo_ldap_message', $response['statusMessage'] . ' Please verify the Search Base(s) and Search filter. Your user should be present in the Search base defined.');
						$this->show_error_message();
					} else if( strcasecmp( $response['statusCode'], 'CURL_ERROR') == 0) {
						update_option('mo_ldap_message', $response['statusMessage']);
						$this->show_error_message();
					} else {
						update_option( 'mo_ldap_message', 'There was an error processing your request. Please verify the Search Base(s) and Search filter. Your user should be present in the Search base defined.');
						$this->show_error_message();
					}		
				}
				else if( $_POST['option'] == "mo_ldap_test_default_auth" ) {		//test default authentication with current settings
					$default_config = get_option('mo_ldap_default_config');
				
					$default_test_username = $default_config['test_username'];
					$default_test_password = $default_config['test_password'];
					
					//Call to test default authentication
					$mo_ldap_config = new Mo_Ldap_Config();
					$content = $mo_ldap_config->test_authentication($default_test_username, $default_test_password, true);
					$response = json_decode( $content, true );
					
					if(strcasecmp($response['statusCode'], 'SUCCESS') == 0) {
						update_option( 'mo_ldap_message', 'Authenticated successfully.');
						$this->show_success_message();
					} else if(strcasecmp($response['statusCode'], 'ERROR') == 0) {
						update_option( 'mo_ldap_message', $response['statusMessage']);
						$this->show_error_message();
					} else if( strcasecmp( $response['statusCode'], 'CURL_ERROR') == 0) {
						update_option('mo_ldap_message', $response['statusMessage']);
						$this->show_error_message();
					} else {
						update_option( 'mo_ldap_message', 'There was an error processing your request.');
						$this->show_error_message();
					}
				}
				else if( $_POST['option'] == "mo_ldap_test_default_config" ) {		//test default connection with current settings
					//Call to test connection
					$mo_ldap_config = new Mo_Ldap_Config();
					$content = $mo_ldap_config->test_connection(true);
					$response = json_decode( $content, true );
					
					if(strcasecmp($response['statusCode'], 'SUCCESS') == 0) {
						update_option( 'mo_ldap_message', 'Connection was established successfully.');
						$this->show_success_message();
					} else if(strcasecmp($response['statusCode'], 'ERROR') == 0) {
						update_option( 'mo_ldap_message', $response['statusMessage']);
						$this->show_error_message();
					} else if( strcasecmp( $response['statusCode'], 'CURL_ERROR') == 0) {
						update_option('mo_ldap_message', $response['statusMessage']);
						$this->show_error_message();
					} else {
						update_option( 'mo_ldap_message', 'There was an error processing your request.');
						$this->show_error_message();
					}
				}
				else if($_POST['option'] == "mo_ldap_login_send_query"){
					$query = '';
					if( Mo_Ldap_Util::check_empty_or_null( $_POST['query_email'] ) || Mo_Ldap_Util::check_empty_or_null( $_POST['query'] ) ) {
						update_option( 'mo_ldap_message', 'Please submit your query along with email.');
						$this->show_error_message();
						return;
					} else{
						$query = sanitize_text_field( $_POST['query'] );
						$email = sanitize_text_field( $_POST['query_email'] );
						$phone = sanitize_text_field( $_POST['query_phone'] );
						$contact_us = new Mo_Ldap_Customer();
						$submited = json_decode($contact_us->submit_contact_us($email, $phone, $query),true);
						
						if( strcasecmp( $submited['status'], 'CURL_ERROR') == 0) {
							update_option('mo_ldap_message', $submited['statusMessage']);
							$this->show_error_message();
						} else if(json_last_error() == JSON_ERROR_NONE) {
							if ( $submited == false ) {
								update_option('mo_ldap_message', 'Your query could not be submitted. Please try again.');
								$this->show_error_message();
							} else {
								update_option('mo_ldap_message', 'Thanks for getting in touch! We shall get back to you shortly.');
								$this->show_success_message();
							}
						}

					}
				}
				else if( $_POST['option'] == "mo_ldap_resend_otp" ) {			//send OTP to user to verify email
					$customer = new Mo_Ldap_Customer();
					$content = json_decode($customer->send_otp_token(), true);
					if(strcasecmp($content['status'], 'SUCCESS') == 0) {
							update_option( 'mo_ldap_message', ' A one time passcode is sent to ' . get_option('mo_ldap_admin_email') . ' again. Please enter the OTP recieved.');
							update_option('mo_ldap_transactionId',$content['txId']);
							update_option('mo_ldap_registration_status','MO_OTP_DELIVERED_SUCCESS');
							$this->show_success_message();
					} else if( strcasecmp( $content['status'], 'CURL_ERROR') == 0) {
						update_option('mo_ldap_message', $content['statusMessage']);
						update_option('mo_ldap_registration_status','MO_OTP_DELIVERED_FAILURE');
						$this->show_error_message();
					} else{
							update_option('mo_ldap_message','There was an error in sending email. Please click on Resend OTP to try again.');
							update_option('mo_ldap_registration_status','MO_OTP_DELIVERED_FAILURE');
							$this->show_error_message();
					}
				}
				else if( $_POST['option'] == "mo_ldap_validate_otp"){		//verify OTP entered by user

					//validation and sanitization
					$otp_token = '';
					if( Mo_Ldap_Util::check_empty_or_null( $_POST['otp_token'] ) ) {
						update_option( 'mo_ldap_message', 'Please enter a value in otp field.');
						update_option('mo_ldap_registration_status','MO_OTP_VALIDATION_FAILURE');
						$this->show_error_message();
						return;
					} else{
						$otp_token = sanitize_text_field( $_POST['otp_token'] );
					}

					$customer = new Mo_Ldap_Customer();
					$content = json_decode($customer->validate_otp_token(get_option('mo_ldap_transactionId'), $otp_token ),true);
					if(strcasecmp($content['status'], 'SUCCESS') == 0) {
						$customer = new Mo_Ldap_Customer();
						$customerKey = json_decode($customer->create_customer(), true);
						if(strcasecmp($customerKey['status'], 'CUSTOMER_USERNAME_ALREADY_EXISTS') == 0) {	//admin already exists in miniOrange
							$content = $customer->get_customer_key();
							$customerKey = json_decode($content, true);
							if(json_last_error() == JSON_ERROR_NONE) {
								$this->save_success_customer_config($customerKey['id'], $customerKey['apiKey'], $customerKey['token'], 'Your account has been retrieved successfully.');
							} else {
								update_option( 'mo_ldap_message', 'You already have an account with miniOrange. Please enter a valid password.');
								update_option('mo_ldap_verify_customer', 'true');
								delete_option('mo_ldap_new_registration');
								$this->show_error_message();
							}
						} else if(strcasecmp($customerKey['status'], 'SUCCESS') == 0) { 	//registration successful
							$this->save_success_customer_config($customerKey['id'], $customerKey['apiKey'], $customerKey['token'], 'Registration complete!');
						}
						update_option('mo_ldap_password', '');
					} else if( strcasecmp( $content['status'], 'CURL_ERROR') == 0) {
						update_option('mo_ldap_message', $content['statusMessage']);
						update_option('mo_ldap_registration_status','MO_OTP_VALIDATION_FAILURE');
						$this->show_error_message();
					} else{
						update_option( 'mo_ldap_message','Invalid one time passcode. Please enter a valid otp.');
						update_option('mo_ldap_registration_status','MO_OTP_VALIDATION_FAILURE');
						$this->show_error_message();
					}
				}
			}
		}
		
		/*
		 * Save all required fields on customer registration/retrieval complete.
		 */
		function save_success_customer_config($id, $apiKey, $token, $message) {
			update_option( 'mo_ldap_admin_customer_key', $id );
			update_option( 'mo_ldap_admin_api_key', $apiKey );
			update_option( 'mo_ldap_customer_token', $token );
			update_option('mo_ldap_password', '');
			update_option( 'mo_ldap_message', $message);
			delete_option('mo_ldap_verify_customer');
			delete_option('mo_ldap_new_registration');
			delete_option('mo_ldap_registration_status');
			$this->show_success_message();
		}
		
		/*
		 * Delelte LDAP Config
		 */
		function delete_ldap_configuration() {
			update_option( 'mo_ldap_server_url', '');
			update_option( 'mo_ldap_server_dn', '');
			update_option( 'mo_ldap_server_password', '');
			update_option( 'mo_ldap_dn_attribute', '');
			update_option( 'mo_ldap_search_base', '');
			update_option( 'mo_ldap_search_filter', '');
		}
		
		function mo_ldap_settings_style() {
			wp_enqueue_style( 'mo_ldap_admin_settings_style', plugins_url('includes/css/style_settings.css', __FILE__));
			wp_enqueue_style( 'mo_ldap_admin_settings_phone_style', plugins_url('includes/css/phone.css', __FILE__));
		}

		function mo_ldap_settings_script() {
			wp_enqueue_script( 'mo_ldap_admin_settings_phone_script', plugins_url('includes/js/phone.js', __FILE__ ));
			wp_enqueue_script( 'mo_ldap_admin_settings_script', plugins_url('includes/js/settings_page.js', __FILE__ ), array('jquery'));
		}
		
		function success_message() {
			$class = "error";
			$message = get_option('mo_ldap_message');
			echo "<div class='" . $class . "'> <p>" . $message . "</p></div>"; 
		}
		
		function error_message() {
			$class = "updated";
			$message = get_option('mo_ldap_message');
			echo "<div class='" . $class . "'> <p>" . $message . "</p></div>"; 
		}
		
		private function show_success_message() {
			remove_action( 'admin_notices', array( $this, 'success_message') );
			add_action( 'admin_notices', array( $this, 'error_message') );
		}
		
		private function show_error_message() {
			remove_action( 'admin_notices', array( $this, 'error_message') );
			add_action( 'admin_notices', array( $this, 'success_message') );
		}
		
		function plugin_query_vars($vars) {
			$vars[] = 'app_name';
			return $vars;
		}
		
		function parse_sso_request($wp){
			if (array_key_exists('app_name', $wp->query_vars)){
				$redirectUrl = mo_ldap_saml_login($wp->query_vars['app_name']);
				wp_redirect($redirectUrl, 302);
				exit;
			}
		}
		
		public function mo_ldap_deactivate() {
			//delete all stored key-value pairs
			if( !Mo_Ldap_Util::check_empty_or_null( get_option('mo_ldap_registration_status') ) ) {
				delete_option('mo_ldap_admin_email');
			}
			
			delete_option('mo_ldap_host_name');
			delete_option('mo_ldap_default_config');
			delete_option('mo_ldap_password');
			delete_option('mo_ldap_new_registration');
			delete_option('mo_ldap_admin_phone');
			delete_option('mo_ldap_verify_customer');
			delete_option('mo_ldap_admin_customer_key');
			delete_option('mo_ldap_admin_api_key');
			delete_option('mo_ldap_customer_token');
			delete_option('mo_ldap_message');
			
			delete_option('mo_ldap_enable_login');
			delete_option('mo_ldap_server_url');
			delete_option('mo_ldap_server_dn');
			delete_option('mo_ldap_server_password');
			delete_option('mo_ldap_dn_attribute');
			delete_option('mo_ldap_search_base');
			delete_option('mo_ldap_search_filter');
			
			delete_option('mo_ldap_transactionId');
			delete_option('mo_ldap_registration_status');
		}
	}
	
	new Mo_Ldap_Login;
?>