<?php

class OpensslAes
{
    private $textToEncrypt = "My super secret information.";
    private $encryptionMethod = "AES-256-CBC";  // AES is used by the U.S. gov't to encrypt top secret documents.
    private $secretHash = "25c6c7ff35b9979b151f2136cd13b0ff";
    
    //To encrypt
    public function encrypt()
    {
        $encryptedMessage = openssl_encrypt($textToEncrypt, $encryptionMethod, $secretHash);
    }
    
    //To Decrypt
    public function decrypt()
    {
        $decryptedMessage = openssl_decrypt($encryptedMessage, $encryptionMethod, $secretHash);
    }
    
    /**
     * simple method to encrypt or decrypt a plain text string
     * initialization vector(IV) has to be the same when encrypting and decrypting
     *
     * @param string $action: can be 'encrypt' or 'decrypt'
     * @param string $string: string to encrypt or decrypt
     *
     * @return string
     */
    public function encrypt_decrypt($action, $string) {
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'This is my secret key';
        $secret_iv = 'This is my secret iv';
        // hash
        $key = hash('sha256', $secret_key);
        
        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ( $action == 'encrypt' ) {
            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        } else if( $action == 'decrypt' ) {
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }
        return $output;
    }
    
    function my_encrypt($data, $key) {
        // Remove the base64 encoding from our key
        $encryption_key = base64_decode($key);
        // Generate an initialization vector
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        // Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $encryption_key, 0, $iv);
        // The $iv is just as important as the key for decrypting, so save it with our encrypted data using a unique separator (::)
        return base64_encode($encrypted . '::' . $iv);
    }
    
    function my_decrypt($data, $key) {
        // Remove the base64 encoding from our key
        $encryption_key = base64_decode($key);
        // To decrypt, split the encrypted data from our IV - our unique separator used was "::"
        list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
        return openssl_decrypt($encrypted_data, 'aes-256-cbc', $encryption_key, 0, $iv);
    }
}

