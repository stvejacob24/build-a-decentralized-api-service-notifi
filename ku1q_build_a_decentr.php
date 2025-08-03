<?php

// API Specification for Decentralized API Service Notifier

class DecentNotif {
    private $api_key;
    private $api_secret;
    private $nodes = [];

    function __construct($api_key, $api_secret) {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
    }

    function addNode($node_url) {
        $this->nodes[] = $node_url;
    }

    function notify($service, $message, $data = []) {
        $signature = hash_hmac('sha256', $service . $message, $this->api_secret);
        $headers = [
            'Api-Key' => $this->api_key,
            'Api-Signature' => $signature
        ];

        foreach ($this->nodes as $node) {
            $ch = curl_init($node . '/' . $service);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response !== false) {
                return true;
            }
        }

        return false;
    }
}

// Example usage
$decentNotif = new DecentNotif('my_api_key', 'my_api_secret');
$decentNotif->addNode('https://node1.example.com/api');
$decentNotif->addNode('https://node2.example.com/api');

if ($decentNotif->notify('user_created', 'User created successfully', ['user_id' => 123, 'username' => 'johnDoe'])) {
    echo "Notification sent successfully";
} else {
    echo "Failed to send notification";
}

?>