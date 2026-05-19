<?php

namespace app\notifications;

use RuntimeException;

class SmsNotification implements NotificationInterface {
    /**
     * @var string
     */
    public const URL = 'https://smspilot.ru/api.php';

    /**
     * @var string
     */
    private string $apiKey;

    /**
     * @var int
     */
    private int $timeout = 15;

    /**
     * @param string $apiKey
     */
    public function __construct(string $apiKey) {
        $this->apiKey = $apiKey;
    }

    /**
     * @param string $message
     * @param string $to
     * @param string $from
     * @return int
     */
    public function send(string $message, string $to, string $from): int {
        $response = file_get_contents(self::URL . '?' . http_build_query([
            'apikey' => $this->apiKey,
            'send' => $message,
            'to' => $to,
            'from' => $from,
            'format' => 'json',
        ]), false, stream_context_create([
            'http' => [
                'timeout' => $this->timeout,
            ],
        ]));

        if ($response === false) {
            throw new RuntimeException('SMS API unreachable');
        }

        $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        if (isset($json['error'])) {
            throw new RuntimeException($json['error']['description'], $json['error']['code']);
        }

        return (int)$json['send'][0];
    }
}
