<?php

	function mo_ldap_saml_login($app_name){

		$email = wp_get_current_user()->user_email;

		$customer_id = get_option("mo_ldap_customer_id");
		$api_key = get_option("mo_ldap_api_key");
		$token_key = get_option("mo_ldap_token_key");

		list($usec, $sec) = explode(" ", microtime());
		$time13 = sprintf('%d%03d', $sec, $usec/1000);

		$token = $email . ':' . $time13 . ':' . $api_key;
		$encryptedToken = mo_ldap_encryptData($token, $token_key);
		$url_encoded = urlencode($encryptedToken);
		$redirectUrl = 'https://auth.miniorange.com/moas/idp/tokenlogin?token=' . $url_encoded . '&app='.$app_name.'&id='.$customer_id.'&encrypted=true';
		return $redirectUrl;
	}

	function mo_ldap_encryptData($str, $key){
		$block = mcrypt_get_block_size('rijndael_128', 'ecb');
		$pad = $block - (strlen($str) % $block);
		$str .= str_repeat(chr($pad), $pad);
		return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $str, MCRYPT_MODE_ECB));
	}

?>