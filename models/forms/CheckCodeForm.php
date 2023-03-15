<?php
namespace app\models\forms;

use yii\base\Model;

class CheckCodeForm extends Model
{
    public $username;
    public $code;

    public function rules()
    {
        return [
            [['username','code'], 'required']
        ];
    }
}
