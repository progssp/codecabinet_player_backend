<?php
    class Utility {
        private $encryption_iv = '1234567891011121';
        private $encryption_key = 'vtechvideos1234';
        private $decryption_iv = '1234567891011121';
        private $decryption_key = 'vtechvideos1234';
        private $ciphering = "AES-128-CTR";
        private $options = 0;

        public function encrypt_data($data){
            $simple_string = $data;
            
            // Store the cipher method
            $ciphering = $this->ciphering;
            
            // Use OpenSSl Encryption method
            $iv_length = openssl_cipher_iv_length($ciphering);
            $options = $this->options;
            
            // Non-NULL Initialization Vector for encryption
            $encryption_iv = $this->encryption_iv;
            
            // Store the encryption key
            $encryption_key = $this->encryption_key;
            
            // Use openssl_encrypt() function to encrypt the data
            $encryption = openssl_encrypt(
                $simple_string, 
                $ciphering,
                $encryption_key, 
                $options, 
                $encryption_iv
            );
            
            // return the encrypted string
            return $encryption;
        }

        public function decrypt_data($data){
            // Use openssl_decrypt() function to decrypt the data
            $decryption = openssl_decrypt(
                $data, 
                $this->ciphering, 
                $this->decryption_key, 
                $this->options, 
                $this->decryption_iv
            );

            // return the decrypted string
            return $decryption;
        }
    }
?>