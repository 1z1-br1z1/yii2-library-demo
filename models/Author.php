<?php

namespace app\models;

/**
 * This is the model class for table "author".
 *
 * @property int $id
 * @property string $fio
 * @property string $created
 * @property string|null $updated
 *
 * @property Book[] $books
 * @property Subscription[] $subscriptions
 */
class Author extends \yii\db\ActiveRecord {
    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['updated'], 'default', 'value' => null],
            [['fio'], 'required'],
            [['fio'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'fio' => 'FIO',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    /**
     * Gets query for [[Books]].
     *
     * @return BookQuery
     */
    public function getBooks() {
        return $this->hasMany(Book::class, ['id' => 'bookId'])->viaTable('book2author', ['authorId' => 'id']);
    }

    /**
     * Gets query for [[Subscriptions]].
     *
     * @return \yii\db\ActiveQuery|SubscriptionQuery
     */
    public function getSubscriptions() {
        return $this->hasMany(Subscription::class, ['authorId' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return AuthorQuery the active query used by this AR class.
     */
    public static function find() {
        return new AuthorQuery(get_called_class());
    }
}
