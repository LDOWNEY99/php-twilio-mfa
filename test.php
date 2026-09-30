<?php

require __DIR__ . '/config.php';

$phone = '+353860382180'; // your verified Twilio phone number

try {
    $verification = $twilio
        ->verify
        ->v2
        ->services($_ENV['TWILIO_VERIFY_SID'])
        ->verifications
        ->create(
            $phone,
            'sms'
        );

    echo 'Verification sent successfully!<br>';
    echo 'Status: ' . $verification->status;

} catch (Throwable $e) {
    echo 'Twilio error: ' . $e->getMessage();
}