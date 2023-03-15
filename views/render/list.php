<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

/* @var $this yii\web\View */
/* @var $data array */
use yii\helpers\Html;
?>
<ul class="info-list">
    <?php foreach($params as $param):?>
    <li class="info-list-item">
        <label class="info-list-title" for="policy-policy_number"><?= Html::encode($labels[$param]) ?></label>
        <span class="info-list-value attribute_policy_number"><?= Html::encode($data[$param]) ?></span>
    </li>
    <?php endforeach; ?>
</ul>