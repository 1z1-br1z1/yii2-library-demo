<?php

namespace app\jobs;

use app\models\Author;
use app\services\NotificationService;
use RuntimeException;

use Yii;
use yii\base\BaseObject;

use yii\queue\JobInterface;

class NotifyAuthorSubscribersJob extends BaseObject implements JobInterface {
    /**
     * @var int
     */
    public $authorId;

    /**
     * @param yii\queue\Queue $queue
     */
    public function execute($queue) {
        if (empty($author = Author::findOne($this->authorId))) {
            throw new RuntimeException('Author not found.');
        }

        Yii::$container->get(NotificationService::class)->notifyAuthorSubscribers($author);
    }
}
