<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "subscription".
 *
 * @property int $id
 * @property int $authorId
 * @property string $phone
 * @property string $created
 * @property string|null $updated
 *
 * @property Author $author
 */
class Subscription extends ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['updated'], 'default', 'value' => null],
            [['authorId', 'phone'], 'required'],
            [['authorId'], 'integer'],
            [['phone'], 'match', 'pattern' => '~^[0-9]{11}$~'],
            [['authorId'], 'exist', 'skipOnError' => true, 'targetClass' => Author::class, 'targetAttribute' => ['authorId' => 'id']],
            [['phone'], 'unique', 'targetAttribute' => ['authorId', 'phone'], 'message' => 'This phone already subscribed on this author.'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'authorId' => 'Author ID',
            'phone' => 'Phone',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    /**
     * Gets query for [[Author]].
     *
     * @return \yii\db\ActiveQuery|AuthorQuery
     */
    public function getAuthor() {
        return $this->hasOne(Author::class, ['id' => 'authorId']);
    }

    /**
     * {@inheritdoc}
     * @return SubscriptionQuery the active query used by this AR class.
     */
    public static function find() {
        return new SubscriptionQuery(get_called_class());
    }
}
