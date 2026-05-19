<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "book".
 *
 * @property int $id
 * @property string $name
 * @property string $note
 * @property string $year
 * @property string|null $isbn
 * @property string|null $photo
 * @property string $created
 * @property string|null $updated
 *
 * @property Author[] $authors
 */
class Book extends ActiveRecord {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'book';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name', 'note', 'year', 'isbn'], 'required'],
            [['name', 'photo'], 'string', 'max' => 255],
            [['note'], 'string'],
            [['year'], 'integer', 'min' => 1, 'max' => 1 + (int)date('Y')],
            [['isbn'], 'filter', 'filter' => static function ($value) {
                return preg_replace('~[^0-9X]~', '', strtoupper($value));
            }],
            [['isbn'], 'match', 'pattern' => '~^([0-9]{9}[0-9X]|[0-9]{13})$~', 'enableClientValidation' => false],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'note' => 'Note',
            'year' => 'Year',
            'isbn' => 'Isbn',
            'photo' => 'Photo',
            'created' => 'Created',
            'updated' => 'Updated',
        ];
    }

    /**
     * Gets query for [[Authors]].
     *
     * @return AuthorQuery
     */
    public function getAuthors() {
        return $this->hasMany(Author::class, ['id' => 'authorId'])->viaTable('book2author', ['bookId' => 'id']);
    }

    /**
     * {@inheritdoc}
     * @return BookQuery the active query used by this AR class.
     */
    public static function find() {
        return new BookQuery(get_called_class());
    }
}
