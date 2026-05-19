<?php

namespace app\models;

use yii\web\UploadedFile;

/**
 *
 */
class BookForm extends Book {
    /**
     * @var string
     */
    const string SCENARIO_CREATE = 'create';

    /**
     * @var UploadedFile|null
     */
    public $imageFile;

    /**
     * @var array
     */
    public $authorsId;

    /**
     * {@inheritdoc}
     */
    public function rules() {
        $rules = parent::rules();

        return [...$rules,
            [['imageFile'], 'image', 'extensions' => ['gif', 'png', 'jpg', 'jpeg', 'webp'], 'mimeTypes' => ['image/gif', 'image/png', 'image/jpeg', 'image/webp'], 'maxSize' => 1024 * 1024],
            [['imageFile'], 'required', 'on' => self::SCENARIO_CREATE],

            [['authorsId'], 'required'],
            [['authorsId'], 'exist', 'allowArray' => true, 'targetClass' => Author::class, 'targetAttribute' => 'id'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return parent::attributeLabels() + [
            'imageFile' => 'Photo',
            'authorsId' => 'Authors',
        ];
    }
}
