<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models;

use Yii;

class GroupScreen extends BaseModel
{
    public static $dataLib = 'CodiacSDK.AdminGroupScreen';
    public static $dataAction = 'GetGroupScreenList';

    /**
     * Getting data from API server
     * @param array $fieldList
     * @param array $postData
     * @param array $additionallyParam
     * @return mixed
     */
    public static function getData($fieldList = [], $postData = [], $additionallyParam = [])
    {
        $model = parent::getData($fieldList, $postData, $additionallyParam);

        if (!empty($model->list)) {
            usort($model->list, function ($a, $b) {
                return ($cmp = strnatcmp($a["weight"], $b["weight"])) ? $cmp : strnatcmp($a["screen_text"], $b["screen_text"]);
            });
        }

        return $model;
    }
}