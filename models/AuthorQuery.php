<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Author]].
 *
 * @see Author
 */
class AuthorQuery extends ActiveQuery {
    /**
     * {@inheritdoc}
     * @return Author[]
     */
    public function all($db = null) {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Author|null
     */
    public function one($db = null) {
        return parent::one($db);
    }
}
