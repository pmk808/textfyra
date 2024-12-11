<?php

namespace App\Services;

class SmsManager
{
    protected $activeService;
    protected $services = [];

    public function addService(string $name, SmsServiceInterface $service)
    {
        $this->services[$name] = $service;
        return $this;
    }

    public function setActiveService(string $name)
    {
        if (!isset($this->services[$name])) {
            throw new \Exception("SMS service '$name' not found");
        }
        $this->activeService = $name;
        return $this;
    }

    public function send($number, $message)
    {
        if (!$this->activeService) {
            throw new \Exception("No active SMS service set");
        }

        return $this->services[$this->activeService]->sendSMS($number, $message);
    }
}