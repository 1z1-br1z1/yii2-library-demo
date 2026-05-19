<?php

namespace app\commands;

use app\jobs\NotifyAuthorSubscribersJob;
use Yii;
use yii\console\Controller;

class TestController extends Controller {
    public function actionIndex() {
        echo Yii::$app->getSecurity()->generatePasswordHash('user');
    }
}
