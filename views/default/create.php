<?php

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $model \Redbox\PersonalSettings\models\SettingModel */

$this->title = Yii::t('yii2mod.settings', 'Create Setting');
$this->params['breadcrumbs'][] = ['label' => Yii::t('yii2mod.settings', 'PersonalSettings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="setting-create">

    <h1><?php echo Html::encode($this->title); ?></h1>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]);
    ?>

</div>
