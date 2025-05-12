<?php
require_once __DIR__ . '/../vendor/autoload.php';

use AfricasTalking\SDK\AfricasTalking;

class Sms {
    private $sms;

    public function __construct() {
        $username = Util::$username; 
        $apiKey = Util::$apikey;
        $AT = new AfricasTalking($username, $apiKey);
        $this->sms = $AT->sms();
    }

    public function sendSMS($message, $recipients) {
        $from = "MyMoney ltd";

        try {
            $result = $this->sms->send([
                'to'      => $recipients,
                'message' => $message,
                'from'    => $from
            ]);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>