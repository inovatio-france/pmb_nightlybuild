<?php
use Otp\GoogleAuthenticator;
use Otp\Otp;
use ParagonIE\ConstantTime\Encoding;
// +-------------------------------------------------+
// © 2002-2010 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: mfa_root.class.php,v 1.3 2023/06/27 13:44:41 jparis Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php"))
    die("no access");

class mfa_root {
    public function generate_secret_code(int $length = 7) : string {
        $secret_code = "";
        $digits = "0123456789";

        for ($i = 0; $i < $length; $i++) {
            $random_digit = $digits[rand(0, strlen($digits) - 1)];
            $secret_code .= $random_digit;
        }
    
        return $secret_code;
    }

    protected function get_key_uri($type, $label, $secret_code, $options = array()) {
        $label = trim($label);

        $otpauth = 'otpauth://' . $type . '/' . rawurlencode($label) . '?secret=' . rawurlencode($secret_code);

        // Defaults to SHA1
        if (array_key_exists('algorithm', $options)) {
            $otpauth .= '&algorithm=' . rawurlencode($options['algorithm']);
        }

        // Defaults to 6
        if (array_key_exists('digits', $options)) {
            $otpauth .= '&digits=' . intval($options['digits']);
        }

        // Defaults to 30
        if (array_key_exists('period', $options)) {
            $otpauth .= '&period=' . rawurlencode($options['period']);
        }

        if (array_key_exists('issuer', $options)) {
            $otpauth .= '&issuer=' . rawurlencode($options['issuer']);
        }

        return $otpauth;
    }

    public function get_qr_code_url($type, $label, $secret_code, $options = array()) {
        $width = 100;
        $height = 100;

        $otpauth = $this->get_key_uri($type, $label, $secret_code, $options);

        $url = 'https://chart.googleapis.com/chart?chs=' . $width . 'x'
             . $height . '&cht=qr&chld=M|0&chl=' . urlencode($otpauth);

        return $url;
    }
}