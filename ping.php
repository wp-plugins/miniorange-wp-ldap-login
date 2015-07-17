<?php

	$data = $_POST['data'];
	$url = 'https://test.miniorange.com/moas/api/ldap/ping';
	$ch = curl_init($url);
				
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
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $data);
	$content = curl_exec($ch);
		
	if(curl_errno($ch)){
		echo 'Request Error:' . curl_error($ch);
	    exit();
	}
		
	echo $content;
	curl_close($ch);

?>