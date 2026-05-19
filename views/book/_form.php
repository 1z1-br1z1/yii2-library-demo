<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\BookForm $model */
/** @var yii\widgets\ActiveForm $form */
/** @var array $authors */

?>

<div class="book-form">
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'note')->textarea() ?>

    <?= $form->field($model, 'year')->textInput() ?>

    <?= $form->field($model, 'isbn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'authorsId')->listBox($authors, [
        'multiple' => true,
    ]) ?>

    <?php if ($model->photo !== ""): ?>
        <div class="form-group">
            <?= Html::img($model->photo, ['width' => 100]) ?>
        </div>
    <?php endif; ?>

    <?= $form->field($model, 'imageFile')->fileInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
