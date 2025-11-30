<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(storage_path('app/firebase/firebase_credentials.json'));
        $this->messaging = $factory->createMessaging();
    }

    public function sendToToken($token, $title, $body)
    {
        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create($title, $body))
            ->withData([
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]);

        return $this->messaging->send($message);
    }
}