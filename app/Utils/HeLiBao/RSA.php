<?php

header("Content-type:text/html;charset='UTF-8");

class Rsa
{

	/*实现加签功能*/
	function genSign($signFormString, $filePath) {
		$priKey = file_get_contents($filePath);
		$res = openssl_get_privatekey($priKey);
		openssl_sign($signFormString, $sign, $res, OPENSSL_ALGO_MD5);
		openssl_free_key($res);
		$sign = base64_encode($sign);
		return $sign;
	}

	/*实现验签功能*/
	function verSign($signFormString, $sign, $filePath)  {
		$pubKey = file_get_contents($filePath);
		$res = openssl_get_publickey($pubKey);
		$result = (bool)openssl_verify($signFormString, base64_decode($sign), $res, OPENSSL_ALGO_MD5);
		openssl_free_key($res);
		if($result) {
			return "true";
		}else {
			return "false";
		}
	}

	/*实现公钥加密功能*/
	function rsaEnc($keyStr, $filePath){
		$res = file_get_contents($filePath);
		$public_key= openssl_pkey_get_public($res);
		openssl_public_encrypt(str_pad($keyStr, 256, "\0", STR_PAD_LEFT), $encrypted, $public_key, OPENSSL_NO_PADDING);
		$jiami = base64_encode($encrypted);
		return $jiami;
	}

}



?>