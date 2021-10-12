<html>
    <head>
        <title>Symetric Encryption Test with OpenSSL</title>
    </head>
    <body>
<?php
//We display a welcome message, if the user is logged, we display it username
$plaintext = "message to be encrypted: THE QUICK BROWN FOX JUMPS OVER THE LAZY DOG + 30 digits: 123456789012345678901234567890";
$cipher = "aes-128-gcm";
$ivlen = openssl_cipher_iv_length($cipher);
echo "AES-128 block size: ".((string)$ivlen)."<BR>";
$iv     = openssl_random_pseudo_bytes($ivlen);
$key    = openssl_random_pseudo_bytes(16);
$tag    = null;
$method = openssl_get_cipher_methods();
echo "Test start:\n";
if (in_array($cipher, $method))
{
    echo "Encrypting: ".$plaintext.'<BR>';
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv, $tag);
    echo "Encrypted message (raw): ".$ciphertext_raw.'<BR>';
    echo "Encrypted message (base64): ".base64_encode($ciphertext_raw).'<BR>';
    echo "Generating MAC:<BR>";
    $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
    echo "Signature (raw): ".$hmac.'<BR>';
    echo "Signature (base64): ".base64_encode($hmac).'<BR>';
    $ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );    //store $cipher, $iv, and $tag for decryption later
    echo "Encrypted message (base64): ".$ciphertext.'<BR>';

    echo "<BR>Decrypting:<BR>";
	$c = base64_decode($ciphertext);
	$iv = substr($c, 0, $ivlen);
    echo "Generating MAC:<BR>";
	$hmac = substr($c, $ivlen, $sha2len=32);
    echo "Signature (raw): ".$hmac.'<BR>';
    echo "Signature (base64): ".base64_encode($hmac).'<BR>';
	$ciphertext_raw = substr($c, $ivlen+$sha2len);
    echo "encrypted message(raw): ".$ciphertext_raw.'<BR>';
    echo "encrypted message (base64): ".base64_encode($ciphertext_raw).'<BR>';
	$decrypted = openssl_decrypt($ciphertext_raw, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv, $tag);
	$calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary=true);
    echo "Checking MAC:<BR>";
	if (hash_equals($hmac, $calcmac))//PHP 5.6+ timing attack safe comparison
	{
		echo ">>>>>>>>>>>>>>>Texto original: ".$decrypted."\n";
	}
	else
	{
		echo "Authentication Failure\n";
	}
}
else
{
	echo "Pane\n";
}
?>
	</body>
</html>