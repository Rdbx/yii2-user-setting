<?php

use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii2mod\editable\EditableColumn;
use Redbox\PersonalSettings\models\enumerables\SettingStatus;
use Redbox\PersonalSettings\models\enumerables\SettingType;
use Redbox\PersonalSettings\models\SettingModel;

/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \Redbox\PersonalSettings\models\search\SettingSearch */

$this->title = Yii::t('yii2mod.settings', 'Settings');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="setting-index">
    <h1><?php echo Html::encode($this->title); ?></h1>

    <p><?php echo Html::a(Yii::t('yii2mod.settings', 'Create Setting'), ['create'], ['class' => 'btn btn-success']); ?></p>
    <?php Pjax::begin(['timeout' => 10000, 'enablePushState' => false]); ?>
    <?php echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
                ],
                [
                    'attribute' => 'type',
                    'filter' => SettingType::listData(),
                    'filterInputOptions' => ['prompt' => Yii::t('yii2mod.settings', 'Select Type'), 'class' => 'form-control'],
                ],
                [
                    'attribute' => 'section',
                    'filter' => ArrayHelper::map(SettingModel::find()->select('section')->distinct()->all(), 'section', 'section'),
                    'filterInputOptions' => ['prompt' => Yii::t('yii2mod.settings', 'Select Section'), 'class' => 'form-control'],
                ],
                'key',
                'value:ntext',
                [
                    'class' => EditableColumn::class,
                    'attribute' => 'status',
                    'url' => ['edit-setting'],
                    'value' => function ($model) {
                        return SettingStatus::getLabel($model->status);
                    },
                    'type' => 'select',
                    'editableOptions' => function ($model) {
                        return [
                            'source' => SettingStatus::listData(),
                            'value' => $model->status,
                        ];
                    },
                    'filter' => SettingStatus::listData(),
                    'filterInputOptions' => ['prompt' => Yii::t('yii2mod.settings', 'Select Status'), 'class' => 'form-control'],
                ],
                'description:ntext',
                [
                    'header' => Yii::t('yii2mod.settings', 'Actions'),
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update}{delete}',
                ],
            ],
        ]
    ); ?>
    <?php Pjax::end(); ?>
</div>
