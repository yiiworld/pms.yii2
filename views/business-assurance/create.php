<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\BusinessAssurance */

$this->title = 'Create Business Assurance';
$this->params['breadcrumbs'][] = ['label' => 'Business Assurance: Select Project', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'Business Assurances', 'url' => ['index', 'projectid'=>Yii::$app->request->get('projectid')]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="business-assurance-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
