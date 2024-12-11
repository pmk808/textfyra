<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class VonageService
{
    protected $apiKey;
    protected $apiSecret;
    protected $senderId;

    public function __construct()
    {
        try {
            $this->apiKey = env('VONAGE_API_KEY');
            $this->apiSecret = env('VONAGE_API_SECRET');
            $this->senderId = env('VONAGE_SENDER_ID', 'Vonage');  
            
            Log::debug('VonageService initialized with:', [
                'apiKey' => $this->apiKey ? 'present' : 'missing',
                'apiSecret' => $this->apiSecret ? 'present' : 'missing',
                'senderId' => $this->senderId
            ]);

            if (!$this->apiKey || !$this->apiSecret) {
                Log::error('Vonage API credentials are missing');
                throw new Exception('Vonage API credentials are not configured');
            }
        } catch (Exception $e) {
            Log::error('VonageService initialization failed:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function sendSMS($number, $message)
    {
        Log::debug('sendSMS method called with:', [
            'number' => $number,
            'messageLength' => strlen($message)
        ]);

        try {
            $formattedNumber = $this->formatPhoneNumber($number);
            Log::debug('Phone number formatted:', ['original' => $number, 'formatted' => $formattedNumber]);

            $basic = new \Vonage\Client\Credentials\Basic($this->apiKey, $this->apiSecret);
            $client = new \Vonage\Client($basic);

            Log::debug('Attempting to send SMS via Vonage');
            
            $response = $client->sms()->send(
                new \Vonage\SMS\Message\SMS($formattedNumber, $this->senderId, $message)
            );

            $currentMessage = $response->current();
            $status = $currentMessage->getStatus();

            Log::debug('Vonage API response:', [
                'status' => $status,
                'response' => json_encode($response)
            ]);

            if ($status === 0) {
                Log::info('SMS sent successfully', [
                    'to' => $formattedNumber,
                    'status' => $status
                ]);

                return [
                    'success' => true,
                    'message' => 'Message sent successfully'
                ];
            } else {
                Log::error('SMS sending failed', [
                    'to' => $formattedNumber,
                    'status' => $status
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to send message',
                    'status' => $status
                ];
            }

        } catch (Exception $e) {
            Log::error('Exception in sendSMS:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error sending message',
                'error' => $e->getMessage()
            ];
        }
    }

    private function formatPhoneNumber($number)
    {
        Log::debug('Formatting phone number:', ['input' => $number]);
        
        // Remove any non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);
        
        // If number starts with 0, replace it with country code
        if (substr($number, 0, 1) === '0') {
            $number = '63' . substr($number, 1);
        }
        
        Log::debug('Phone number formatted:', ['output' => $number]);
        return $number;
    }
}