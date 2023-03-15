<?php

namespace app\models\forms;

use kartik\password\StrengthValidator;
use Yii;
use yii\base\Model;
use app\models\UserAccount;

class ChangePasswordForm extends Model
{
	public $username;
    public $oldPassword;
    public $password;
    public $repeat;
    private $user = false;


    public function __construct(UserAccount $user)
    {
        $this->user = $user;
    }

    public function rules()
    {
        return [
            [['oldPassword', 'password', 'repeat'], 'required'],
            ['password', StrengthValidator::class, 'preset' => 'normal'],
            ['repeat', 'compare', 'compareAttribute' => 'password'],
            ['oldPassword', 'validatePassword'],
			[['username'], 'safe']
        ];
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {

            if ($this->user->account_password !== UserAccount::encodePassword($this->oldPassword)) {
                $this->addError($attribute, 'Incorrect password.');
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'oldPassword' => Yii::t('app', 'Old password'),
            'password' => Yii::t('app', 'Password'),
            'repeat' => Yii::t('app', 'Repeat password'),
        ];
    }
}