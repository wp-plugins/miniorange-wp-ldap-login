<?php

/*Main function*/
function mo_ldap_settings() {
	if( isset( $_GET[ 'tab' ] ) ) {
		$active_tab = $_GET[ 'tab' ];
	} else {
		$active_tab = 'default';
	}
	?>
	<h2>LDAP/Active Directory Login for Cloud</h2>
	<?php
		if(!Mo_Ldap_Util::is_curl_installed()) {
			?>
			
			<div id="help_curl_warning_title" class="mo_ldap_title_panel">
				<p><a target="_blank" style="cursor: pointer;"><font color="#FF0000">Warning: PHP cURL extension is not installed or disabled. <span style="color:blue">Click here</span> for instructions to enable it.</font></a></p>
			</div>
			<div hidden="" id="help_curl_warning_desc" class="mo_ldap_help_desc">
					<ul>
						<li>Step 1:&nbsp;&nbsp;&nbsp;&nbsp;Open php.ini file located under php installation folder.</li>
						<li>Step 2:&nbsp;&nbsp;&nbsp;&nbsp;Search for <b>extension=php_curl.dll</b> </li>
						<li>Step 3:&nbsp;&nbsp;&nbsp;&nbsp;Uncomment it by removing the semi-colon(<b>;</b>) in front of it.</li>
						<li>Step 4:&nbsp;&nbsp;&nbsp;&nbsp;Restart the Apache Server.</li>
					</ul>
					For any further queries, please <a href="mailto:info@miniorange.com">contact us</a>.								
			</div>
					
			<?php
		}
		if(!Mo_Ldap_Util::is_extension_installed('mcrypt')) {
			?>
			<font color="#FF0000">(Warning: <a target="_blank" href="http://php.net/manual/en/mcrypt.installation.php">PHP mcrypt extension</a> is not installed or disabled)</font> <span id="help_mcrypt_warning_title" class="mo_ldap_title_panel">
				<a target="_blank" style="cursor: pointer;"><span style="color:blue">(Why we need it?)</span></a></span>
			
			<div hidden="" id="help_mcrypt_warning_desc" class="mo_ldap_help_desc">
					<ul>
						<li>PHP Mcrypt extension is required to Encrypt LDAP configuration in such a way as to make it unreadable by anyone except those possessing special knowledge (usually referred to as a "key") that allows them to change the information back to its original, readable form.</li>
						<li>Encryption is important because it allows you to securely protect your LDAP configuration that you don't want anyone else to have access to.</li>
					</ul>
					For any further queries, please <a href="mailto:info@miniorange.com">contact us</a>.								
			</div>		
			<?php
		}
		
	?>
	<div class="mo2f_container">
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php echo $active_tab == 'default' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'default'), $_SERVER['REQUEST_URI'] ); ?>">Test Default LDAP</a>
			<a class="nav-tab <?php echo $active_tab == 'config' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'config'), $_SERVER['REQUEST_URI'] ); ?>">LDAP Configuration</a>
			<a class="nav-tab <?php echo $active_tab == 'troubleshooting' ? 'nav-tab-active' : ''; ?>" href="<?php echo add_query_arg( array('tab' => 'troubleshooting'), $_SERVER['REQUEST_URI'] ); ?>">Troubleshooting</a>
		</h2>
		<table style="width:100%;">
			<tr>
				<td style="width:65%;vertical-align:top;">
					<?php
							if($active_tab == "config") {
								if (get_option ( 'mo_ldap_verify_customer' ) == 'true') {
									mo_ldap_login_page();
								} else if (trim ( get_option ( 'mo_ldap_admin_email' ) ) != '' && trim ( get_option ( 'mo_ldap_admin_api_key' ) ) == '' && get_option ( 'mo_ldap_new_registration' ) != 'true') {
									mo_ldap_login_page();
								} else if(get_option('mo_ldap_registration_status') == 'MO_OTP_DELIVERED_SUCCESS' || get_option('mo_ldap_registration_status') == 'MO_OTP_VALIDATION_FAILURE' || get_option('mo_ldap_registration_status') == 'MO_OTP_DELIVERED_FAILURE'){
									mo_ldap_show_otp_verification();
								}else if (! Mo_Ldap_Util::is_customer_registered()) {
									mo_ldap_registration_page();
								} else {
									mo_ldap_configuration_page();
								}
							} else if($active_tab == 'troubleshooting'){ 
								mo_ldap_troubleshooting();
							} else {
								mo_ldap_default_config_page();
							}
					?>
				</td>
				<td style="vertical-align:top;padding-left:1%;">
					<?php echo mo_ldap_support(); ?>
				</td>
			</tr>
		</table>
	</div>
	<?php
}
/*End of main function*/

/* Create Customer function */
function mo_ldap_registration_page(){
	update_option ( 'mo_ldap_new_registration', 'true' );
	?>

<!--Register with miniOrange-->
<form name="f" method="post" action="">
	<input type="hidden" name="option" value="mo_ldap_register_customer" />
	<p>Just complete the short registration below to configure your own LDAP Server. Or you can test using our LDAP Server. Please enter a valid email id that you have access to. You will be able to move forward after verifying an OTP that we will send to this email.</p>
	<div class="mo_ldap_table_layout" style="min-height: 274px;">
		<h3>Register with miniOrange</h3>
		<div id="panel1">
			<table class="mo_ldap_settings_table">
				<tr>
					<td><b><font color="#FF0000">*</font>Email:</b></td>
					<td><input class="mo_ldap_table_textbox" type="email" name="email"
						required placeholder="person@example.com"
						value="<?php echo get_option('mo_ldap_admin_email');?>" /></td>
				</tr>

				<tr>
					<td><b><font color="#FF0000">*</font>Phone number:</b></td>
					<td><input class="mo_ldap_table_textbox" type="tel" id="phone"
						pattern="[\+]?[0-9]{1,4}\s?[0-9]{10}" name="phone" required
						title="Phone with country code eg. +1xxxxxxxxxx"
						placeholder="Phone with country code eg. +1xxxxxxxxxx"
						value="<?php echo get_option('mo_ldap_admin_phone');?>" /></td>
				</tr>
				<tr>
					<td><b><font color="#FF0000">*</font>Password:</b></td>
					<td><input class="mo_ldap_table_textbox" required type="password"
						name="password" placeholder="Choose your password (Min. length 6)" />
					</td>
				</tr>
				<tr>
					<td><b><font color="#FF0000">*</font>Confirm Password:</b></td>
					<td><input class="mo_ldap_table_textbox" required type="password"
						name="confirmPassword" placeholder="Confirm your password" /></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" value="Save"
						class="button button-primary button-large" /></td>
				</tr>
			</table>
		</div>
	</div>
</form>
<script>
	jQuery("#phone").intlTelInput();
</script>
<?php
}
/* End of Create Customer function */

/* Login for customer*/
function mo_ldap_login_page() {
	?>
		<!--Verify password with miniOrange-->
		<form name="f" method="post" action="">
			<input type="hidden" name="option" value="mo_ldap_verify_customer" />
			<div class="mo_ldap_table_layout">
				<h3>Login with miniOrange</h3>
				<div id="panel1">
					<table class="mo_ldap_settings_table">
						<tr>
							<td><b><font color="#FF0000">*</font>Email:</b></td>
							<td><input class="mo_ldap_table_textbox" type="email" name="email"
								required placeholder="person@example.com"
								value="<?php echo get_option('mo_ldap_admin_email');?>" /></td>
						</tr>
						<tr>
							<td><b><font color="#FF0000">*</font>Password:</b></td>
							<td><input class="mo_ldap_table_textbox" required type="password"
								name="password" placeholder="Enter your miniOrange password" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><input type="submit" class="button button-primary button-large" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a
								target="_blank"
								href="<?php echo get_option('mo_ldap_host_name') . "/moas/idp/userforgotpassword"; ?>">Forgot
									your password?</a></td>
						</tr>
					</table>
				</div>
			</div>
		</form>
	<?php
}
/* End of Login for customer*/

function mo_ldap_link() {
	?>
	<a href="http://miniorange.com/wordpress-ldap-login" style="display:none">Login to WordPress using LDAP</a>
	<a href="http://miniorange.com/cloud-identity-broker-service" style="display:none">Cloud Identity broker service</a>
	<?php
}

/* Configure LDAP function */
function mo_ldap_configuration_page(){
	$default_config = get_option('mo_ldap_default_config');

	$server_url = isset($_POST['ldap_server']) ? $_POST['ldap_server'] :
		( get_option('mo_ldap_server_url') ? Mo_Ldap_Util::decrypt(get_option('mo_ldap_server_url')) : '');
	$dn = isset($_POST['dn']) ? $_POST['dn'] :
		(get_option('mo_ldap_server_dn') ? Mo_Ldap_Util::decrypt(get_option('mo_ldap_server_dn')) : '');
	$admin_password = isset($_POST['admin_password']) ? $_POST['admin_password'] :
		(get_option('mo_ldap_server_password') ? Mo_Ldap_Util::decrypt(get_option('mo_ldap_server_password')) : '');
	$dn_attribute = isset($_POST['dn_attribute']) ? $_POST['dn_attribute'] :
		(get_option('mo_ldap_dn_attribute') ? Mo_Ldap_Util::decrypt(get_option('mo_ldap_dn_attribute')) : '');
	$search_base = isset($_POST['search_base']) ? $_POST['search_base'] :
		(get_option('mo_ldap_search_base') ? Mo_Ldap_Util::decrypt(get_option('mo_ldap_search_base')) : '');
	$search_filter = isset($_POST['search_filter']) ? $_POST['search_filter'] :
		(get_option('mo_ldap_search_filter') ? Mo_Ldap_Util::decrypt(get_option('mo_ldap_search_filter')) : '');
	?>
		<div class="mo_ldap_small_layout" style="margin-top:0px;">
			<!-- Toggle checkbox -->
			<form name="f" id="enable_login_form" method="post" action="">
				<input type="hidden" name="option" value="mo_ldap_enable" />
				<h3>Enable login using LDAP</h3>
				
				
				
				<?php 
					$serverUrl = get_option('mo_ldap_server_url');
					if(isset($serverUrl) && $serverUrl != ''){?>
						<input type="checkbox" id="enable_ldap_login" name="enable_ldap_login" value="1" <?php checked(get_option('mo_ldap_enable_login') == 1);?> />Enable LDAP login
				<?php } else{?>
						<input type="checkbox" id="enable_ldap_login" name="enable_ldap_login" value="1" <?php checked(get_option('mo_ldap_enable_login') == 1);?> disabled />Enable LDAP login
				<?php }?>
				<p>Enabling LDAP login will protect your login page by your configured LDAP. <b>Please check this only after you have successfully tested your configuration</b> as the default WordPress login will stop working.</p>
			</form>
			<script>
				jQuery('#enable_ldap_login').change(function() {
					jQuery('#enable_login_form').submit();
				});
			</script>
			<br/>
			<!-- Toggle checkbox -->
			<form name="f" id="enable_register_user_form" method="post" action="">
				<input type="hidden" name="option" value="mo_ldap_register_user" />
				<input type="checkbox" id="mo_ldap_register_user" name="mo_ldap_register_user" value="1" <?php checked(get_option('mo_ldap_register_user') == 1);?> />Enable Auto Registering users if they do not exist in WordPress
				</form>
			<script>
				jQuery('#mo_ldap_register_user').change(function() {
					jQuery('#enable_register_user_form').submit();
				});
			</script>
			<br/>
		</div>

		<div class="mo_ldap_small_layout">
			<script>
				function ping_server(){

					var isFirewallAllowed = document.getElementById('firewall_allowed').checked;

					if(!isFirewallAllowed){
						alert("Check the above option to confirm that you have allowed access to the LDAP server from the given IP addresses.");
					} else{
						var ldapServerUrl = document.getElementById('ldap_server').value;
						if(!ldapServerUrl || ldapServerUrl.trim() == ""){
							alert("Enter LDAP Server URL");
						} else{
							var option = document.getElementById("mo_ldap_configuration_form_action").value = "mo_ldap_ping_server";
							//alert(document.getElementById("mo_ldap_configuration_form_action").value);
							var configForm = document.getElementById("mo_form1");
							//alert(configForm);
							configForm.submit();
						}
					}
				}
			</script>
			<!-- Save LDAP Configuration -->
			<form id="mo_form1" name="f" method="post" action="">
				<input id="mo_ldap_configuration_form_action" type="hidden" name="option" value="mo_ldap_save_config" />
				<!-- Copy default values to configuration -->
				<p><strong style="font-size:14px;">NOTE: </strong> You need to find out the values for the below given fields from your LDAP Administrator.</strong></p>
				<p><strong style="font-size:14px;">NOTE: </strong>You need to allow incoming requests from hosts - <font style="color:blue">52.6.168.155</font> and <font style="color:blue">52.6.204.243</font> by a firewall rule for the port <font style="color:blue">389</font>(<font style="color:blue">636</font> for SSL or ldaps) on LDAP Server.</p>
				<h3 class="mo_ldap_left">LDAP Connection Information</h3>
				<ul>
					<li><input type="checkbox" name="firewall_allowed" id="firewall_allowed" /> <b>Allowed incoming requests from hosts - <font style="color:blue">52.6.168.155</font> and <font style="color:blue">52.6.204.243</font> by a firewall rule for the port <font style="color:blue">389</font>(<font style="color:blue">636</font> for SSL or ldaps) on LDAP Server.</b></li>
				</ul>
				<div id="panel1">
					<table class="mo_ldap_settings_table">
						<tr>
							<td style="width: 24%"><b><font color="#FF0000">*</font>LDAP Server:</b></td>
							<td><input class="mo_ldap_table_textbox" type="url" id="ldap_server" name="ldap_server" required placeholder="ldap://<server_address or IP>:<port>" value="<?php echo $server_url?>" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><i>Specify the host name for the LDAP server eg: ldap://myldapserver.domain:389 , ldap://89.38.192.1:389. When using SSL, the host may have to take the form ldaps://host:636.</i></td>
						</tr>
						<tr>
							<td></td>
							<td><input type="button" class="button button-primary button-large" onclick="ping_server();" value="Contact LDAP Server" />&nbsp;&nbsp;<span id="pingResult"></span></td>
							<td></td>
						</tr>
						<tr>
							<td></td>
							<td float="right"><i>Confirm connection to your LDAP server from <font style="color:blue">52.6.168.155</font> , <font style="color:blue">52.6.204.243</font> through port <font style="color:blue">389</font>(<font style="color:blue">636</font> for SSL or ldaps).</i></td>
						</tr>
						<tr><td>&nbsp;</td></tr>
						<tr>
							<td><b><font color="#FF0000">*</font>Service Account DN:</b></td>
							<td><input class="mo_ldap_table_textbox" type="text" id="dn" name="dn" required placeholder="CN=service,DC=domain,DC=com" value="<?php echo $dn?>" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><i>Specify the Service Account DN(distinguished Name) of the LDAP server. e.g. cn=username,cn=group,dc=domain,dc=com<br/>uid=username,ou=organisational unit,dc=domain,dc=com.</i></td>
						</tr>
						<tr><td>&nbsp;</td></tr>
						<tr>
							<td><b><font color="#FF0000">*</font>Admin Password:</b></td>
							<td><input class="mo_ldap_table_textbox" required type="password" name="admin_password" placeholder="Enter password of Service Account" value="<?php echo $admin_password?>"/></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><i>Password for the Service Account in the LDAP Server.</i></td>
						</tr>
						<tr><td>&nbsp;</td></tr>
					</table>
				</div>
				<h3>LDAP User Mapping Configuration</h3>
				<div id="panel1">
					<table class="mo_ldap_settings_table">
						<tr>
							<td style="width: 24%"><b><font color="#FF0000">*</font>DN Attribute:</b></td>
							<td><input class="mo_ldap_table_textbox" type="text" id="dn_attribute" name="dn_attribute" required placeholder="distinguishedName" value="<?php echo $dn_attribute?>" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><i>Attribute in LDAP which stores unique DN value. eg: distinguishedName in AD, entryDN in OpenLDAP.</i></td>
						</tr>
						<tr><td>&nbsp;</td></tr>
						<tr>
							<td><b><font color="#FF0000">*</font>SearchBase(s):</b></td>
							<td><input class="mo_ldap_table_textbox" type="text" id="search_base" name="search_base" required placeholder="dc=domain,dc=com" value="<?php echo $search_base?>" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><i>Specify the base DN of the LDAP server or organisational unit that should used as a base for all LDAP searches. eg. cn=Users,dc=domain,dc=com. For multiple searchBase use semicolon(;) separated values. eg. cn=Users,dc=domain,dc=com; ou=people,dc=domian,dc=com<br/>dc=domain,dc=com</i></td>
						</tr>
						<tr><td>&nbsp;</td></tr>
						<tr>
							<td><b><font color="#FF0000">*</font>LDAP Search Filter:</b></td>
							<td><input class="mo_ldap_table_textbox" type="text" id="search_filter" name="search_filter" required placeholder="(&(objectClass=*)(cn=?))" value="<?php echo $search_filter?>" /></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><i>It is a basic LDAP Query for searching users based on mapping of username to a particular LDAP attribute. Format: <b>(&(objectClass=*)(&lt;LDAP_ATTRIBUTE&gt;=?))</b>. Replace <b>&lt;LDAP_ATTRIBUTE&gt;</b> with the attribute where your username is stored. Some common attributes are 
							<ol>
							<table>
								<tr><td style="width:50%">common name</td><td>(&(objectClass=*)(<b>cn</b>=?))</td></tr>
								<tr><td>email</td><td>(&(objectClass=*)(<b>mail</b>=?))</td></tr> 
								<tr><td>logon name</td><td>(&(objectClass=*)(<b>sAMAccountName</b>=?))<br/>(&(objectClass=*)(<b>userPrincipalName</b>=?))</td></tr>
								<tr><td>custom attribute where you store your WordPress usernames use</td> <td>(&(objectClass=*)(<b>customAttribute</b>=?))</td></tr>
								<tr><td>if you store Wordpress usernames in multiple attributes(eg: some users login using email and others using their username)</td><td>(&(objectClass=*)(<b>|</b>(<b>cn=?</b>)(<b>mail=?</b>)))</td></tr>
							</table>
							</ol>
						</tr>
						<tr><td>&nbsp;</td></tr>
						<tr>
							<td>&nbsp;</td>
							<td><input type="submit" class="button button-primary button-large" value="Test Connection & Save"/>&nbsp;&nbsp; <input
								type="button" id="conn_help" class="help" value="Troubleshooting" /></td>
						</tr>
						<tr>
							<td colspan="2" id="conn_troubleshoot" hidden>
								<p>
									<strong>Are you having trouble connecting to your LDAP server from this plugin?</strong>
									<ol>
										<li>Please check to make sure that all the values entered in the <b>LDAP Connection Information</b> section are correct.</li>
										<li>If all those values are correct, then you need to make sure that if there is a firewall, you open the firewall to allow incoming requests to your LDAP. Please open port 389(636 for SSL or ldaps). Host - 52.6.168.155 , 52.6.204.243 - This is the host from where the LDAP connection as well as authentication requests are going to be made.</li>
										<li>If you are still having problems, submit a query using the support panel on the right hand side.</li>
									</ol>
								</p>
							</td>
						</tr>
					</table>
				</div>
			</form>
		</div>
		<div class="mo_ldap_small_layout">
		<!-- Authenticate with LDAP configuration -->
		<form name="f" method="post" action="">
			<input type="hidden" name="option" value="mo_ldap_test_auth" />
			<h3>Test Authentication</h3>
			<div id="test_conn_msg"></div>
			<div id="panel1">
				<table class="mo_ldap_settings_table">
					<tr>
						<td style="width: 24%"><b><font color="#FF0000">*</font>Username:</b></td>
						<td><input class="mo_ldap_table_textbox" type="text" name="test_username" required placeholder="Enter username"/></td>
					</tr>
					<tr>
						<td><b><font color="#FF0000">*</font>Password:</b></td>
						<td><input class="mo_ldap_table_textbox" type="password" name="test_password" required placeholder="Enter password" /></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><input type="submit" class="button button-primary button-large" value="Test Authentication"/>&nbsp;&nbsp; <input
								type="button" id="auth_help" class="help" value="Troubleshooting" /></td>
					</tr>
					<tr>
						<td colspan="2" id="auth_troubleshoot" hidden>
							<p>
								<strong>User is not getting authenticated? Check the following:</strong>
								<ol>
									<li>The username-password you are entering is correct.</li>
									<li>The user is present in the search bases you have specified against <b>SearchBase(s)</b> above.</li>
								</ol>
							</p>
						</td>
					</tr>
				</table>
			</div>
		</form>
		</div>
	<?php
}
/* End of Configure LDAP function */

/* Test Default Configuration*/
function mo_ldap_default_config_page() {
	$default_config = get_option('mo_ldap_default_config');
	?>
	<div class="mo_ldap_table_layout">
		<!-- Test connection for default configuration -->
		<form name="f" method="post" action="">
			<input type="hidden" name="option" value="mo_ldap_test_default_config" />

				<p>Test with miniOrange LDAP Server default configuration. </p>
				<p><b><font color="#FF0000">NOTE : The values given below are mock values. They are not the ones acually being used to make the connection. So if you try copying them to the LDAP configuration tab, IT WILL NOT WORK.</font></b> You need to provide actual LDAP configuration in the LDAP configuration tab. If you need any help, please contact us at info@miniorange.com</p>
				<h3>Test Connection</h3>
				<div id="panel1">
					<table class="mo_ldap_settings_table">
						<tr>
							<td><b>LDAP Server:</b></td>
							<td style="width:65%"><input class="mo_ldap_table_textbox fixed" type="url" value="<?php echo $default_config['server_url'];?>" readonly/></td>
						</tr>
						<tr>
							<td><b>Service Account DN:</b></td>
							<td><input class="mo_ldap_table_textbox fixed" type="text" name="dn" value="<?php echo $default_config['service_account_dn']; ?>" readonly/></td>
						</tr>
						<tr>
							<td><b>Admin Password:</b></td>
							<td><input class="mo_ldap_table_textbox fixed" type="password" value="<?php echo $default_config['admin_password']; ?>" readonly/></td>
						</tr>
						<tr>
							<td><b>DN Attribute:</b></td>
							<td><input class="mo_ldap_table_textbox fixed" type="text" value="<?php echo $default_config['dn_attribute']; ?>" readonly/></td>
						</tr>
						<tr>
							<td><b>SearchBase:</b></td>
							<td><input class="mo_ldap_table_textbox fixed" type="text" value="<?php echo $default_config['search_base'];?>" readonly/></td>
						</tr>
						<tr>
							<td><b>LDAP Search Filter:</b></td>
							<td><input class="mo_ldap_table_textbox fixed" type="text" value="<?php echo $default_config['search_filter'];?>" readonly/></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td><input type="submit" class="button button-primary button-large" value="Test Connection"/></td>
						</tr>
					</table>
				</div>
		</form>

		<!-- Test authentication for default configuration-->
		<form name="f" method="post" action="">
			<input type="hidden" name="option" value="mo_ldap_test_default_auth" />
			<h3>Test Authentication</h3>
			<div id="test_conn_msg"></div>
			<div id="panel1">
				<table class="mo_ldap_settings_table">
					<tr>
						<td><b><font color="#FF0000">*</font>Username:</b></td>
						<td style="width:65%"><input class="mo_ldap_table_textbox fixed" type="text" readonly value="<?php echo $default_config['test_username'];?>"/></td>
					</tr>
					<tr>
						<td><b><font color="#FF0000">*</font>Password:</b></td>
						<td><input class="mo_ldap_table_textbox fixed" type="password" readonly value="<?php echo $default_config['test_password'];?>"/></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td><input type="submit" class="button button-primary button-large" value="Test Authentication"/></td>
					</tr>
				</table>
			</div>
		</form>
	</div>
	<?php
}
/* End of Test Default Configuration*/

/* Show OTP verification page*/
function mo_ldap_show_otp_verification(){
	?>
		<div class="mo_ldap_table_layout">
			<div id="panel2">
				<table class="mo_ldap_settings_table">
		<!-- Enter otp -->
					<form name="f" method="post" id="ldap_form" action="">
						<input type="hidden" name="option" value="mo_ldap_validate_otp" />
						<h3>Verify Your Email</h3>
						<tr>
							<td><b><font color="#FF0000">*</font>Enter OTP:</b></td>
							<td colspan="2"><input class="mo_ldap_table_textbox" autofocus="true" type="text" name="otp_token" required placeholder="Enter OTP" style="width:61%;" pattern="{6,8}"/>
							 &nbsp;&nbsp;<a style="cursor:pointer;" onclick="document.getElementById('resend_otp_form').submit();">Resend OTP</a></td>
						</tr>
						<tr><td colspan="3"></td></tr>
						<tr>
							<td>&nbsp;</td>
							<td>
							<input type="submit" value="Validate OTP" class="button button-primary button-large" />
							<a id="back_button" href=""class="button button-primary button-large">Cancel</a>
							</td>

					</form>
					<form name="f" id="resend_otp_form" method="post" action="">
							<td>
							<input type="hidden" name="option" value="mo_ldap_resend_otp"/>
							</td>
						</tr>
					</form>
				</table>
			</div>
		</div>
		<script>
			jQuery('#back_button').click(function() {
				<?php update_option( 'mo_ldap_registration_status', '' );?>
				window.location.reload();
			}
		</script>
<?php
}
/* End Show OTP verification page*/


function mo_ldap_troubleshooting(){
	?>
	
	<div class="mo_ldap_table_layout">
		<table class="mo_ldap_help">
					<tbody><tr>
						<td class="mo_ldap_help_cell">
							<div id="help_curl_title" class="mo_ldap_title_panel">
								<div class="mo_ldap_help_title">How to enable PHP cURL extension? (Pre-requisite)</div>
							</div>
							<div hidden="" id="help_curl_desc" class="mo_ldap_help_desc" style="display: none;">
								<ul>
									<li>Step 1:&nbsp;&nbsp;&nbsp;&nbsp;Open php.ini file located under php installation folder.</li>
									<li>Step 2:&nbsp;&nbsp;&nbsp;&nbsp;Search for <b>extension=php_curl.dll</b>. </li>
									<li>Step 3:&nbsp;&nbsp;&nbsp;&nbsp;Uncomment it by removing the semi-colon(<b>;</b>) in front of it.</li>
									<li>Step 4:&nbsp;&nbsp;&nbsp;&nbsp;Restart the Apache Server.</li>
								</ul>
								For any further queries, please contact us.								
							</div>
						</td>
					</tr>
				
					<tr>
						<td class="mo_ldap_help_cell">
							<div id="help_ldap_title" class="mo_ldap_title_panel">
								<div class="mo_ldap_help_title">Allow access from Firewall (Pre-requisite)</div>
							</div>
							<div hidden="" id="help_ldap_desc" class="mo_ldap_help_desc" style="display: none;">
								<ul>
									<li>You need to allow incoming requests from hosts - <font style="color:blue">52.6.168.155</font> and <font style="color:blue">52.6.204.243</font> by a firewall rule for the port <font style="color:blue">389</font>(<font style="color:blue">636</font> for SSL or ldaps) on LDAP Server.</li>
									<li>You can follow steps here : <a href="http://www.rackspace.com/knowledge_center/article/create-an-inbound-port-allow-rule-for-windows-firewall-2008" target="_blank">http://www.rackspace.com/knowledge_center/article/create-an-inbound-port-allow-rule-for-windows-firewall-2008</a></li>
								</ul>
								For any further queries, please contact us.								
							</div>
						</td>
					</tr>
					
					<tr>
						<td class="mo_ldap_help_cell">
						<div id="help_ping_title" class="mo_ldap_title_panel">
								<div class="mo_ldap_help_title">Why is Contact LDAP Server not working?</div>
							</div>
							<div hidden="" id="help_ping_desc" class="mo_ldap_help_desc" style="display: none;">
								<ul>
									<li>1.&nbsp;&nbsp;&nbsp;&nbsp;Check your LDAP Server URL to see if it is correct.<br>
									 eg. ldap://myldapserver.domain:389 , ldap://89.38.192.1:389. When using SSL, the host may have to take the form ldaps://host:636.</li>
									<li>2.&nbsp;&nbsp;&nbsp;&nbsp;Your LDAP Server may be behind a firewall. Check if the firewall is open to allow requests from hosts - <font style="color:blue">52.6.168.155</font> and <font style="color:blue">52.6.204.243</font> and the port <font style="color:blue">389</font>(<font style="color:blue">636</font> for SSL or ldaps) on LDAP Server.</li>
								</ul>
								For any further queries, please contact us.								
							</div>
						</td>
					</tr>
					
					<tr>
						<td class="mo_ldap_help_cell">
							<div id="help_invaliddn_title" class="mo_ldap_title_panel">
								<div class="mo_ldap_help_title">Why is Test LDAP Configuration not working?</div>
							</div>
							<div hidden="" id="help_invaliddn_desc" class="mo_ldap_help_desc" style="display: none;">
								<ul>
									<li>1.&nbsp;&nbsp;&nbsp;&nbsp;Check if you have entered valid Service Account DN(distinguished Name) of the LDAP server. <br>e.g. cn=username,cn=group,dc=domain,dc=com<br>
									uid=username,ou=organisational unit,dc=domain,dc=com</li>
									<li>2.&nbsp;&nbsp;&nbsp;&nbsp;Check if you have entered correct Password for the Service Account.</li>
								</ul>
								For any further queries, please contact us.								
							</div>
						</td>
					</tr>
					
					<tr>
						<td class="mo_ldap_help_cell">
							<div id="help_invalidsf_title" class="mo_ldap_title_panel">
								<div class="mo_ldap_help_title">Why is Test Authentication not working?</div>
							</div>
							<div hidden="" id="help_invalidsf_desc" class="mo_ldap_help_desc" style="display: none;">
								<ul>
									<li>1.&nbsp;&nbsp;&nbsp;&nbsp;The username/password combination you provided may be incorrect.</li>
									<li>2.&nbsp;&nbsp;&nbsp;&nbsp;You may have provided a <b>Search Base(s)</b> in which the user does not exist.</li>
									<li>3.&nbsp;&nbsp;&nbsp;&nbsp;Your <b>Search Filter</b> may be incorrect and the username mapping may be to an LDAP attribute other than the ones provided in the Search Filter</li>
									<li>4.&nbsp;&nbsp;&nbsp;&nbsp;You may have provided an incorrect <b>Distinguished Name attribute</b> for your LDAP Server.
								</ul>
								For any further queries, please contact us.								
							</div>
						</td>
					</tr>
					
					<tr>
						<td class="mo_ldap_help_cell">
							<div id="help_seracccre_title" class="mo_ldap_title_panel">
								<div class="mo_ldap_help_title">What are the LDAP Service Account Credentials?</div>
							</div>
							<div hidden="" id="help_seracccre_desc" class="mo_ldap_help_desc" style="display: none;">
								<ul>
									<li>1.&nbsp;&nbsp;&nbsp;&nbsp;Service account is an non privileged user which is used to bind to the LDAP Server. It is the preferred method of binding to the LDAP Server if you have to perform search operations on the directory.</li>
									<li>2.&nbsp;&nbsp;&nbsp;&nbsp;The distinguished name(DN) of the service account object and the password are provided as credentials.</li>
								</ul>
								For any further queries, please contact us.								
							</div>
						</td>
					</tr>
					
					<tr>
						<td class="mo_ldap_help_cell">
							<div id="help_sbase_title" class="mo_ldap_title_panel">
								<div class="mo_ldap_help_title">What is meant by Search Base in my LDAP environment?</div>
							</div>
							<div hidden="" id="help_sbase_desc" class="mo_ldap_help_desc" style="display: none;">
								<ul>
									<li>1.&nbsp;&nbsp;&nbsp;&nbsp;Search Base denotes the location in the directory where the search for a particular directory object begins.</li>
									<li>2.&nbsp;&nbsp;&nbsp;&nbsp;It is denoted as the distinguished name of the search base directory object. eg: CN=Users,DC=domain,DC=com.</li>
								</ul>
								For any further queries, please contact us.								
							</div>
						</td>
					</tr>
					
					<tr>
						<td class="mo_ldap_help_cell">
							<div id="help_sfilter_title" class="mo_ldap_title_panel">
								<div class="mo_ldap_help_title">What is meant by Search Filter in my LDAP environment?</div>
							</div>
							<div hidden="" id="help_sfilter_desc" class="mo_ldap_help_desc" style="display: none;">
								<ul>
									<li>1.&nbsp;&nbsp;&nbsp;&nbsp;Search Filter is a basic LDAP Query for searching users based on mapping of username to a particular LDAP attribute.</li>
									<li>2.&nbsp;&nbsp;&nbsp;&nbsp;The following are some commonly used Search Filters. You will need to use a search filter which uses the attributes specific to your LDAP environment. Confirm from your LDAP administrator.</li>
										<ul>
											<table>
												<tr><td style="width:50%">common name</td><td>(&(objectClass=*)(<b>cn</b>=?))</td></tr>
												<tr><td>email</td><td>(&(objectClass=*)(<b>mail</b>=?))</td></tr> 
												<tr><td>logon name</td><td>(&(objectClass=*)(<b>sAMAccountName</b>=?))<br/>(&(objectClass=*)(<b>userPrincipalName</b>=?))</td></tr>
												<tr><td>custom attribute where you store your WordPress usernames use</td> <td>(&(objectClass=*)(<b>customAttribute</b>=?))</td></tr>
												<tr><td>if you store Wordpress usernames in multiple attributes(eg: some users login using email and others using their username)</td><td>(&(objectClass=*)(<b>|</b>(<b>cn=?</b>)(<b>mail=?</b>)))</td></tr>
											</table>
										</ul>
								</ul>
								For any further queries, please contact us.								
							</div>
						</td>
					</tr>
					
					<tr>
						<td class="mo_ldap_help_cell">
							<div id="help_ou_title" class="mo_ldap_title_panel">
								<div class="mo_ldap_help_title">How do users present in different Organizational Units(OU's) login into Wordpress?</div>
							</div>
							<div hidden="" id="help_ou_desc" class="mo_ldap_help_desc" style="display: none;">
								<ul>
									<li>1.&nbsp;&nbsp;&nbsp;&nbsp;You can provide multiple search bases seperated by a semi-colon to ensure users present in different OU's are able to login into Wordpress.</li>
									<li>2.&nbsp;&nbsp;&nbsp;&nbsp;You can also provide the RootDN value in the Search Base so that users in all subtrees of the RootDN are able to login.</li>
								</ul>
								For any further queries, please contact us.								
							</div>
						</td>
					</tr>
					
					<tr>
						<td class="mo_ldap_help_cell">
							<div id="help_loginusing_title" class="mo_ldap_title_panel">
								<div class="mo_ldap_help_title">Some of my users login using their email and the rest using their usernames. How will both of them be able to login?</div>
							</div>
							<div hidden="" id="help_loginusing_desc" class="mo_ldap_help_desc" style="display: none;">
								<ul>
									<li>1.&nbsp;&nbsp;&nbsp;&nbsp;You need to provide a search filter which checks for the username against multiple LDAP attributes.</li>
									<li>2.&nbsp;&nbsp;&nbsp;&nbsp;For example, if you have some users who login using their email and some using their username, the following search filter can be applied: (&(objectClass=*)(|(mail=?)(cn=?)))</li>
								</ul>
								For any further queries, please contact us.								
							</div>
						</td>
					</tr>
					
					<tr>
						<td class="mo_ldap_help_cell">
							<div id="help_diffdist_title" class="mo_ldap_title_panel">
								<div class="mo_ldap_help_title">What are the different Distinguished Name attributes?</div>
							</div>
							<div hidden="" id="help_diffdist_desc" class="mo_ldap_help_desc" style="display: none;">
								<ul>
									<li>1.&nbsp;&nbsp;&nbsp;&nbsp;The distinguished name attribute depends on the LDAP environment.</li>
									<li>2.&nbsp;&nbsp;&nbsp;&nbsp;For example, Active Directory(AD) uses distinguishedName to store the Distinguished Name(DN) attribute</li>
								</ul>
								For any further queries, please contact us.								
							</div>
						</td>
					</tr>
					
				</tbody></table>
	</div>
	
	
	<?php

}


?>