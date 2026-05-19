<?php

use yii\helpers\Html;
use yii\web\YiiAsset;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Book $model */
/** @var \yii\web\User $user */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Books', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

YiiAsset::register($this);

?>
<div class="book-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (!$user->getIsGuest()): ?>
        <p>
            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]) ?>
        </p>
    <?php endif; ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'note',
            'year',
            'isbn',
            [
                'attribute' => 'photo',
                'format' => 'html',
                'value' => function ($model) {
                    return Html::img($model->photo);
                },
            ],
            [
                'attribute' => 'authors',
                'format' => 'html',
                'value' => function ($model) {
                    $authors = [];

                    foreach ($model->authors as $author) {
                        $authors[] = Html::a($author->fio, ['author/view', 'id' => $author->id]);
                    }

                    return join(', ', $authors);
                },
            ],
            'created',
            'updated',
        ],
    ]) ?>

</div>
