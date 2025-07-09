<?php

require_once 'vendor/autoload.php';

use ParagonIE\Halite\Alerts\CannotPerformOperation;
use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\KeyFactory;

$keyPath = 'encryption.key';
try {
    $encryptionKey = KeyFactory::generateEncryptionKey();
    KeyFactory::save($encryptionKey, $keyPath);
} catch (CannotPerformOperation|InvalidKey|SodiumException $e) {
}

