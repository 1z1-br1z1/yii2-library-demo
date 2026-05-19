<?php

namespace app\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Book]].
 *
 * @see Book
 */
class BookQuery extends ActiveQuery {
    /**
     * {@inheritdoc}
     * @return Book[]
     */
    public function all($db = null) {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Book|null
     */
    public function one($db = null) {
        return parent::one($db);
    }
}
