<?php
namespace app\models\forms;

use app\models\AuthenticationModel;
use yii\base\Model;
use Yii;

class ForgotForm extends Model
{
    public $username;
    public $source = 'email';

    public function getSources(){
        return [
            AuthenticationModel::AUTH_SOURCE_EMAIL => 'By email',
            AuthenticationModel::AUTH_SOURCE_SMS => 'By phone (text)'
        ];
    }

    public function rules()
    {
        return [
            [['source','username'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'source' => Yii::t('app', 'Pick your delivery method'),
            'username' => Yii::t('app', 'User name'),
        ];
    }
}
