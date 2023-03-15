<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

/* @var $this yii\web\View */
/* @var $message string */
/* @var $exception Exception */

?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head></head>
    <body style="background: #fff;">
        <div style="width: 100%; height: 100%;">
            <div style="color: #a94442; background-color: #f2dede; padding: 15px; border: 1px solid #ebccd1; border-radius: 4px;">
                <?= nl2br($message) ?>
            </div>
        </div>
    </body>
</html>
