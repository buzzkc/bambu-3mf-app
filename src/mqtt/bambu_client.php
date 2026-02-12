<?php
require __DIR__ . '/../vendor/autoload.php';

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

function bambu_connect(array $printer): MqttClient {
    $settings = (new ConnectionSettings)
        ->setUsername($printer['mqtt_user'])
        ->setPassword($printer['mqtt_access_code'])
        ->setUseTls(true)
        ->setTlsSelfSignedAllowed(true)
        ->setTlsVerifyPeer(false)
        ->setTlsVerifyPeerName(false)
		->setConnectTimeout(5)
		->setSocketTimeout(5)
		->setKeepAliveInterval(20);

    $client = new MqttClient($printer['ip_address'], 8883);
    $client->connect($settings, true);
    return $client;
}
