<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\controllers;

use app\models\UserAccount;
use Yii;
use yii\web\Controller;

class BaseController extends Controller
{
    public function beforeAction($action)
    {
        if (($settings = UserAccount::getSettings()) && isset($settings->user_language)) {
            Yii::$app->language = $settings->user_language;
        } else {
            Yii::$app->language = Yii::$app->sourceLanguage;
        }
        return parent::beforeAction($action);
    }
}
