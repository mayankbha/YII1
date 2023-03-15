<?php
namespace app\modules\chat\models;

use app\models\UserAccount;
use Yii;

class Notification extends BaseModel
{
    public static $dataAction = 'GetAllMessagesNotifications';

    public static function getNewNotifications()
    {
		//$userModel = Yii::$app->session['screenData'][UserAccount::class];

		if(isset(Yii::$app->session['screenData'][UserAccount::class]))
			$userModel = Yii::$app->session['screenData'][UserAccount::class];
		else
			return false;

        if (empty($userModel->id)) {
            return false;
        }

        $result = (new static())->processData([
            'lib_name' => static::$dataLib,
            'func_name' => self::$dataAction,
            'func_param' => [
                'user' => $userModel->id
            ]
        ]);

        return (!empty($result['notify_count'])) ? $result['notify_count'] : '';
    }
}
