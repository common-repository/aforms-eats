<?php

namespace AFormsEats\Infra;

class GoogleScorer 
{
    const URL = 'https://www.google.com/recaptcha/api/siteverify';

    public function __invoke($token, $secretKey, $action) 
    {
        $args = array(
            'method' => 'POST', 
            'body' => array(
                'secret' => $secretKey, 
                'response' => $token
            )
        );
        $result = wp_remote_post(self::URL, $args);
        if (is_wp_error($result)) {
            return false;
        }
        if (wp_remote_retrieve_response_code($result) != 200) {
            return false;
        }
        $response = json_decode(wp_remote_retrieve_body($result), true);
        if (!$response['success'] || $response['action'] != $action) {
            return false;
        }

        return $response['score'];
    }

    /*public function __invoke($token, $secretKey, $action) 
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => self::URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => array(
                'secret' => $secretKey, 
                'response' => $token
            ),
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        if (!$response['success'] || $response['action'] != $action) {
            return false;
        }
        return $response['score'];
    }*/
}