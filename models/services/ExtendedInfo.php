<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models\services;

use Yii;
use yii\helpers\ArrayHelper;

class ExtendedInfo
{
    const CACHE_NAME_INFO = 'extendedinfo';
    const CACHE_NAME_ERROR = 'lastErrorMessageFromAPI';

    public static function setExtendInfoML(array $extendedinfo = [])
    {
        $extendedinfo = ArrayHelper::getColumn($extendedinfo, 'err_description');
        if (isset($extendedinfo[0])) {
            if (!Yii::$app->session->get(self::CACHE_NAME_ERROR)) {
                Yii::$app->session->set(self::CACHE_NAME_ERROR, $extendedinfo[0]);
                unset($extendedinfo[0]);
            }

            self::setExtendInfo($extendedinfo);
        }
    }

    public static function setExtendInfo(array $extendedinfo = [])
    {
        $existExtendedInfo = Yii::$app->session->get(self::CACHE_NAME_INFO) ? Yii::$app->session->get(self::CACHE_NAME_INFO) : [];
        $extendedinfo = array_merge($existExtendedInfo, $extendedinfo);

        Yii::$app->session->set(self::CACHE_NAME_INFO, $extendedinfo);
    }

    public static function getErrorMessage()
    {
        $message = Yii::$app->session->get(self::CACHE_NAME_ERROR);
        Yii::$app->session->set(self::CACHE_NAME_ERROR, null);

        return $message;
    }

    public static function getInfoList()
    {
        $extendedinfo = Yii::$app->session->get(self::CACHE_NAME_INFO);
        Yii::$app->session->set(self::CACHE_NAME_INFO, null);

        return $extendedinfo;
    }
}