<?php

namespace app\services;

use app\models\Author;
use app\notifications\NotificationInterface;

class NotificationService {
    /**
     * @var NotificationInterface
     */
    private NotificationInterface $notification;

    /**
     * @var string
     */
    private string $senderName;

    /**
     * @param NotificationInterface $notification
     * @param string $senderName
     */
    public function __construct(NotificationInterface $notification, string $senderName) {
        $this->notification = $notification;
        $this->senderName = $senderName;
    }

    public function notifyAuthorSubscribers(Author $author): void {
        $message = "У автора `{$author->fio}` вышли новые книги.";

        foreach ($author->getSubscriptions()->each(32) as $subscription) {
            $this->notification->send($message, $subscription->phone, $this->senderName);
        }
    }
}
