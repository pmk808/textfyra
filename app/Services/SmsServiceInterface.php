<?php

namespace App\Services;

interface SmsServiceInterface
{
    public function sendSMS($number, $message);
}