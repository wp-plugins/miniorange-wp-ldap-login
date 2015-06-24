<?php 
    /*
    Plugin Name: miniOrange LDAP Login
    Plugin URI: http://miniorange.com
    Description: Plugin for login into Wordpress through credentials stored in LDAP
    Author: miniorange
    Version: 1.0
    Author URI: http://miniorange.com
    */
	
	require_once 'generate_saml_assertion.php';
	
	class wp_ldap_login{
		
		function __construct(){
			add_action('admin_menu', array($this, 'wp_ldap_login_widget_menu'));
			add_action('admin_init', array($this, 'login_widget_save_options'));
			add_action('parse_request', array($this, 'parse_sso_request'));
			remove_action( 'admin_notices', array( $this, 'success_message') );
			remove_action( 'admin_notices', array( $this, 'error_message') );
			add_filter('query_vars', array($this, 'plugin_query_vars'));
			
			if(get_option('mo_ldap_enable_ldap_login') == 1){
				remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
				add_filter('authenticate', array($this, 'ldap_login'), 20, 3);
			}
			
			
		}
		function success_message() {
			$class = "error";
			$message = get_option('message');
			echo "<div class='" . $class . "'> <p>" . $message . "</p></div>"; 
		}
		
		function error_message() {
			$class = "updated";
			$message = get_option('message');
			echo "<div class='" . $class . "'> <p>" . $message . "</p></div>"; 
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
			
			$gateway_url = get_option('ldap_gateway_url');
			global $post;
			//Send cURL request to gateway url and parse response
				
			//calls to encrypt username and password
			$encrypted_username = $this->encrypt($username);
			$encrypted_password = $this->encrypt($password);			
					
			//send post request to gateway url
			$data = array("userName" => $encrypted_username, "password" => $encrypted_password);
			
			$data_string = json_encode($data);
									
			$curl = curl_init();
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $gateway_url."/rest/ldapauth/login",
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $data_string,
				CURLOPT_HTTPHEADER => array(                                                                          
					'Content-Type: application/json',                                                                                
					'Content-Length: ' . strlen($data_string))
			));
			$response = curl_exec($curl);
			if (curl_errno($curl)) {
				   print curl_error($curl);
			} 
			$decoded_response = (array)json_decode($response);

			curl_close($curl);
					
			$status = $decoded_response['statusCode'];
			if($status == 'SUCCESS'){
			  if( username_exists( $username)) {
				  $user = get_userdatabylogin($username);
				  
				  return $user;
			   } else {
				  return $error;
			   }
						
				wp_redirect( site_url() );
				exit;
						
			} else {
				return $error;
			}
		}
	
		function encrypt($str){
			$key = get_option("mo_ldap_secret_key");
			$block = mcrypt_get_block_size('rijndael_128', 'ecb');
			$pad = $block - (strlen($str) % $block);
			$str .= str_repeat(chr($pad), $pad);
			return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $str, MCRYPT_MODE_ECB));
		}

		
		function wp_ldap_login_widget_menu(){
			add_options_page('LDAP Login Config', 'LDAP Login Config', 'activate_plugins', 'wp_ldap_login_widget', array( $this, 'wp_ldap_login_widget_options'));
		}
		
		function login_widget_save_options(){
			if(isset($_POST['option'])){
				if($_POST['option'] == "login_widget_save_options"){	
				
					//Check and sanitize inputs
					$gateway_url = "";
					$secret_key = "";
					$enable_ldap_login = 0;
					if(isset($_POST["gateway_url"]))
						$gateway_url = sanitize_text_field($_POST["gateway_url"]);
					if(isset($_POST["mo_ldap_secret_key"]))
						$secret_key = sanitize_text_field($_POST["mo_ldap_secret_key"]);
					if(isset($_POST["enable_ldap_login"]))
						$enable_ldap_login = sanitize_text_field($_POST["enable_ldap_login"]);
					
					if(!empty($gateway_url) and !empty($secret_key)){
						update_option("mo_ldap_gateway_url", $gateway_url);
						update_option("mo_ldap_secret_key", $secret_key);
						update_option("mo_ldap_enable_ldap_login", $enable_ldap_login);
						update_option("message", "miniOrange Gateway Configuration saved");
						$this->show_success_message();
					} else{
						update_option("message", "Error saving miniOrange Gateway Configuration");
						$this->show_error_message();
					}
					
				} else if($_POST['option'] == "sso_config_options"){
					
					//Check and sanitize inputs
					$customer_id = "";
					$api_key = "";
					$token_key = "";
					if(isset($_POST["customer_id"]))
						$customer_id = sanitize_text_field($_POST["customer_id"]);
					if(isset($_POST["api_key"]))
						$api_key = sanitize_text_field($_POST["api_key"]);
					if(isset($_POST["token_key"]))
						$token_key = sanitize_text_field($_POST["token_key"]);
					
					if(!empty($customer_id) and !empty($api_key) and !empty($token_key)){	
						update_option("mo_ldap_customer_id", $customer_id);
						update_option("mo_ldap_api_key", $api_key);
						update_option("mo_ldap_token_key", $token_key);
						update_option("message", "SSO Configuration saved");
						$this->show_success_message();
					} else{
						update_option("message", "Error saving SSO Configuration");
						$this->show_error_message();
					}
					
				}
			}
		}
		
		private function show_success_message() {
			remove_action( 'admin_notices', array( $this, 'success_message') );
			add_action( 'admin_notices', array( $this, 'error_message') );
		}
		
		private function show_error_message() {
			remove_action( 'admin_notices', array( $this, 'error_message') );
			add_action( 'admin_notices', array( $this, 'success_message') );
		}
		
		function wp_ldap_login_widget_options(){
			
			$gateway_url = get_option("mo_ldap_gateway_url");
			$enable_ldap_login = get_option("mo_ldap_enable_ldap_login");
			$customer_id = get_option("mo_ldap_customer_id");
			$api_key = get_option("mo_ldap_api_key");
			$token_key = get_option("mo_ldap_token_key");
			$secret_key = get_option("mo_ldap_secret_key");
			?>
			<h2>miniOrange LDAP Login</h2>
			<form name="gateway_config_form" method="post" action="">
				<input type="hidden" name="option" value="login_widget_save_options" />
				<table width="98%" border="0" style="background-color:#FFFFFF; border:1px solid #CCCCCC; padding:0px 0px 0px 10px; margin:2px;">
				   <tr>
					<td width="45%"><h3>miniOrange LDAP Gateway Configuration</h3></td>
					<td width="55%">&nbsp;</td>
				  </tr>
				   <tr>
					<td><input type="checkbox" name="enable_ldap_login" value="1" <?php checked($enable_ldap_login == 1);?> /><strong>Enable LDAP login</strong></td>
					<td>&nbsp;</td>
				  </tr>
				   <tr>
					<td><strong>Enter miniOrange LDAP Gateway URL:</strong></td>
					<td><input type="url" name="gateway_url" style="width:50%;" value="<?php echo $gateway_url;?>" required /></td>
				  </tr>
				  <tr>
					<td><strong>Enter miniOrange LDAP Key:</strong></td>
					<td><input type="text" name="mo_ldap_secret_key" style="width:50%;" value="<?php echo $secret_key;?>" required /></td>
				  </tr>
				  <tr>
					<td colspan="2"></td>
				  </tr>
				  <tr>
					<td>&nbsp;</td>
					<td><br><input type="submit" name="submit" value="Save Gateway Configuration" class="button button-primary button-large" /></td>
				  </tr>
				  <tr>
						<td colspan="2"> 
						<p>
							<strong>Instructions:</strong>
							<ol>
								<li>The URL denotes where the miniOrange Gateway resides. The server where the gateway is hosted needs to be accessible from your WordPress instance. Ensure that appropriate firewall rules are in place</li>
								<li>LDAP Configuration is done in the miniOrange Gateway. The following information is required in order to configure connection to the LDAP Server</li>
								<li><ul>
									<li>a. LDAP Connection String -> Connection string for the LDAP Server. eg: ldap://myldapserver.domain:port</li>
									<li>b. Service Account Distinguished Name(DN). eg: cn=admin,dc=domain,dc=com</li>
									<li>c. Server Account Password</li>
									<li>d. DistinguishedName Attribute (DN Attribute) -> attribute in LDAP which stores unique DN value. eg: distinguishedName in AD, entryDN in OpenLDAP</li>
									<li>e. SearchBase -> Define where users logging in will be located in the LDAP Environment</li>
									<li>f. Search Filter -> It is a basic LDAP Query for searching of user based on mapping of username to a particular attribute. eg: <b>(&(objectClass=*)(cn=?))</b></li>
									<!-- TO DO ATFER MARKETING PAGE IS UP
									li>Include instructions to add link and configure SAML app in marketing page. Add a link from here</li-->
								</ul>
								</li>
								<li><b>Please email us at info@miniorange.com for configuration of miniOrange Gateway. Also mention the information given above.</b></li>
						</p>
						</td>
					</tr>
				</table>
				</form>
				<form name="sso_config_form" method="post" action="">
				  <input type="hidden" name="option" value="sso_config_options" />
				  <table width="98%" border="0" style="background-color:#FFFFFF; border:1px solid #CCCCCC; padding:0px 0px 0px 10px; margin:2px;">
				  <tr>
					<td width="45%"><h3>SSO Configuration</h3></td>
					<td width="55%">&nbsp;</td>
				  </tr>
				  <tr>
					<td colspan="2">If you want to enable access to SAML-enabled cloud apps through miniOrange, configure the following details.</td>
				  </tr>
				  <tr>
					<td><strong>Enter miniOrange Customer Key:</strong></td>
					<td><input type="text" name="customer_id" style="width:50%;" value="<?php echo $customer_id;?>" required /></td>
				  </tr>
				  <tr>
					<td><strong>Enter miniOrange Customer API Key:</strong></td>
					<td><input type="text" name="api_key" style="width:50%;" value="<?php echo $api_key;?>" required /></td>
				  </tr>
				  <tr>
					<td><strong>Enter miniOrange Customer Token Key:</strong></td>
					<td><input type="text" name="token_key" style="width:50%;" value="<?php echo $token_key;?>" required /></td>
				  </tr>
				  <tr>
					<td>&nbsp;</td>
					<td><br><input type="submit" name="submit" value="Save SSO Configuration" class="button button-primary button-large" /></td>
				  </tr>
					<tr>
						<td colspan="2"> 
						<p>
							<strong>Instructions:</strong>
							<ol>
								<li>Login to your <a href="https://auth.miniorange.com/moas">miniOrange</a> account</li>
								<li>Go to <i>Integrations->Custom App Integration</i> from the menu</li>
								<li>Copy the Customer Key, Customer API Key and Customer Token Key to the above textboxes and click on Save.</li>
								<li>To add an app, create a link anywhere on your Wordpress site with the following URL: <b>##wordpress_site_url##</b>/index.php?app_name=<b>##APP_NAME##</b> where APP_NAME corresponds to Application Provider Name of app in miniOrange.</li>
								<li>If you face any issue, email us at info@miniorange.com</li>
							</ol>
						</p>
						</td>
					</tr>
				</table>
			</form>
		<?php
			
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
		
	}
	
	new wp_ldap_login;
?>
