<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\modules\chat\controllers;

use app\models\CommandData;
use app\models\UserAccount;
use Yii;
use app\modules\chat\models\Notification;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class ChatController extends BaseController
{
    public function actionIndex()
    {
        if (!($securityFilter = UserAccount::getSecurityFilter()) || $securityFilter['allow_chat'] != 'Y') {
            throw new ForbiddenHttpException('Access denied');
        }

        $settings = UserAccount::getSettings();

        return $this->render('index', [
            'settings' => $settings
        ]);
    }

    public function actionGetNotifications()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return Notification::getNewNotifications();
    }

    public function actionGetUserList($name)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Your browser sent a request that this server could not understand');
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (empty(Yii::$app->getUser()->getIdentity()->group_area)) {
            throw new \ErrorException('Access denied');
        }

        $groupAreas = Yii::$app->getUser()->getIdentity()->group_area;
        $groupAreas = explode(';', $groupAreas);

        $config = new \stdClass();
        $config->data_source_get = 'SearchUser';
        $config->func_inparam_configuration = ['account_name', 'group_area'];

        Yii::$app->response->format = Response::FORMAT_JSON;
        return CommandData::searchDefault('CodiacSDK.AdminUsers', ['account_name' => $name, 'group_area' => $groupAreas], $config);
    }
}