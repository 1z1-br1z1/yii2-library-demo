<?php

namespace app\notifications;

interface NotificationInterface {
    /**
     * @param string $message
     * @param string $to
     * @param string $from
     * @return mixed
     */
    public function send(string $message, string $to, string $from);
}
