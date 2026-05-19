<?php

use yii\grid\GridView;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string|null $year */
/** @var array $years */

$this->title = 'Топ авторы по годам';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="author-top">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= Html::beginForm('', 'get') ?>
        <div class="form-group">
            <?= Html::listBox('year', $year, $years, [
                'prompt' => 'Все...',
                'class' => 'form-control',
            ]) ?>
        </div>
        <div class="form-group">
            <?= Html::submitButton('Отфильтровать', [
                'class' => 'btn btn-primary',
            ]) ?>
        </div>
    <?= Html::endForm() ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'fio',
            'count',
        ],
    ]); ?>
</div>