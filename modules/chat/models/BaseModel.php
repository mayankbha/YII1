<?php
namespace app\modules\chat\models;

use Yii;
use app\models\BaseModel as CommonBaseModel;

class BaseModel extends CommonBaseModel
{
    const SEARCH_LIMIT = 15;

    public static $dataLib = 'CodiacSDK.Messages';
}
