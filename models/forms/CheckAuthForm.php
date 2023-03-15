<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models\forms;

use Yii;
use yii\base\Model;

class CheckAuthForm extends Model
{
    public $auth_type;
    public $confirmation_code;

    public function rules()
    {
        return [
            [['confirmation_code'], 'required'],
        ];
    }
}