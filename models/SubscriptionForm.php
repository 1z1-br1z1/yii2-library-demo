<?php

namespace app\models;

/**
 * SubscriptionForm model.
 */
class SubscriptionForm extends Subscription {
    public $verifyCode;

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return array_merge(parent::rules(), [
            ['verifyCode', 'captcha'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return array_merge(parent::attributeLabels(), [
            'verifyCode' => 'Verification Code',
        ]);
    }
}
