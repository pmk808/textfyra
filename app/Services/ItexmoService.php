<?php

namespace App\Services;

class ItexmoService
{
    protected $apiCode;

    public function __construct()
    {
        $this->apiCode = env('ITEXMO_API_CODE');
    }

    public function sendSMS($number, $message)
    {
        $url = 'https://www.itexmo.com/php_api/api.php';
        $itexmo = array(
            '1' => $number, 
            '2' => $message, 
            '3' => $this->apiCode
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($itexmo));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $this->getResponseMessage($response);
    }

    private function getResponseMessage($code)
    {
        $messages = [
            '0' => 'Message sent successfully',
            '1' => 'Invalid number',
            '2' => 'Invalid API code',
            '3' => 'Invalid API password',
            '4' => 'API is disabled',
            '5' => 'Invalid recipient',
            '6' => 'System error',
            '7' => 'No load available',
            '8' => 'API registration not activated',
            '9' => 'Invalid parameter',
            '10' => 'Recipient prefix not supported',
            '11' => 'Invalid sender name',
            '12' => 'Invalid sender number'
        ];

        return $messages[$code] ?? 'Unknown error';
    }
}