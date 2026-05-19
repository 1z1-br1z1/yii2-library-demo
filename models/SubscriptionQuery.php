<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Subscription]].
 *
 * @see Subscription
 */
class SubscriptionQuery extends ActiveQuery {
    /**
     * {@inheritdoc}
     * @return Subscription[]
     */
    public function all($db = null) {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Subscription|null
     */
    public function one($db = null) {
        return parent::one($db);
    }
}
