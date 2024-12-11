<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class D7SmsService implements SmsServiceInterface
{
    protected $apiEndpoint = 'https://d7sms.p.rapidapi.com';
    protected $apiKey;
    protected $apiHost;

    public function __construct()
    {
        $this->apiKey = env('D7SMS_RAPID_API_KEY');
        $this->apiHost = env('D7SMS_RAPID_API_HOST');
    }

    public function sendSMS($number, $message)
    {
        try {
            $response = Http::withHeaders([
                'x-rapidapi-key' => $this->apiKey,
                'x-rapidapi-host' => $this->apiHost,
                'content-type' => 'application/json'
            ])->post($this->apiEndpoint . '/messages', [
                'messages' => [
                    [
                        'channel' => 'sms',
                        'recipients' => [$number],
                        'content' => $message,
                        'msg_type' => 'text',
                        'data_coding' => 'text'
                    ]
                ]
            ]);

            if ($response->successful()) {
                return 'Message sent successfully';
            }

            return 'Failed to send message: ' . ($response->json()['message'] ?? 'Unknown error');

        } catch (\Exception $e) {
            return 'Error sending message: ' . $e->getMessage();
        }
    }
}