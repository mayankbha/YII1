<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\components;

use Yii;
use app\models\UserAccount;
use yii\helpers\ArrayHelper;

class AuthHelper
{
    const AUTH_TYPE_LOGIN = 'AuthType.L';
    const AUTH_TYPE_QUESTIONS = 'AuthType.SQ';
    const AUTH_TYPE_EMAIL = 'AuthType.E';
    const AUTH_TYPE_SMS = 'AuthType.S';
    const AUTH_TYPE_SAML = 'AuthType.SAML';

    const STATUS_NOT_INIT = 'NI';
    const STATUS_PROGRESS = 'PROGRESS';
    const STATUS_COMPLETED = 'COMPLETED';

    const CACHE_CONST = 'authType';

    protected static function setCacheData($types, $status = self::STATUS_PROGRESS)
    {
        ArrayHelper::removeValue($types, 'AuthType.LD');
        Yii::$app->session[self::CACHE_CONST] = [
            'types' => $types,
            'status' => $status
        ];

        return true;
    }

    public static function init()
    {
        $userModel = Yii::$app->session['screenData'][UserAccount::class];

        $authTypes = [];
        if (!empty($userModel->auth_required)) {
            $authTypes = explode(';', $userModel->auth_required);
        }

        if (!in_array(self::AUTH_TYPE_LOGIN, $authTypes)) {
            $authTypes[] = self::AUTH_TYPE_LOGIN;
        }

        return self::setCacheData($authTypes);
    }

    /**
     * @return bool|array
     */
    public static function getTypes()
    {
        if (self::getStatus() == self::STATUS_PROGRESS && !empty(Yii::$app->session[self::CACHE_CONST]['types'])) {
            return Yii::$app->session[self::CACHE_CONST]['types'];
        }

        return false;
    }

    public static function getStatus() {
        if (!empty(Yii::$app->session[self::CACHE_CONST]['status'])) {
            return Yii::$app->session[self::CACHE_CONST]['status'];
        }

        return self::STATUS_NOT_INIT;
    }

    public static function getCurrentType()
    {
        if (self::getStatus() == self::STATUS_PROGRESS && $types = self::getTypes()) {
            return current($types);
        }

        return false;
    }

    public static function completeType($type)
    {
        if (($authTypes = self::getTypes()) && in_array($type, [self::AUTH_TYPE_EMAIL, self::AUTH_TYPE_LOGIN, self::AUTH_TYPE_QUESTIONS, self::AUTH_TYPE_SMS])) {
            ArrayHelper::removeValue($authTypes, $type);
            $status = empty($authTypes) ? self::STATUS_COMPLETED : self::STATUS_PROGRESS;

            self::setCacheData($authTypes, $status);
            return true;
        }

        return false;
    }
}