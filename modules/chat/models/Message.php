<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 **************************************************************************
 Date			Developer				Task ID			Description
 2019/07/25		Mayank Bhatnagar		33				Need to change getList() function name to _getList() as same function is used in BaseController.php so it shows error as here its static but in BaseController.php its not static.
 **************************************************************************
 */
 
namespace app\modules\chat\models;

use app\models\UserAccount;
use Yii;

class Message extends BaseModel
{
    const FUNCTION_CREATE = 'CreateMessageCheckAccess';
    const FUNCTION_DELETE = 'DeleteMessageCheckAccess';
    const FUNCTION_UPDATE = 'UpdateMessageCheckAccess';
    const FUNCTION_GET_LIST = 'GetNewMessages'; //'GetMessageListCheckAccess';
    const FUNCTION_SEARCH = 'SearchMessage';

    const READ_ONLY_RIGHTS = 'R';
    const FULL_RIGHTS= 'U';

    public static function create($room, $message, $file = null)
    {
        $userModel = Yii::$app->session['screenData'][UserAccount::class];

        if (empty($userModel->id)) {
            return false;
        }

        $model = new static();
        return $model->processData([
            'lib_name' => static::$dataLib,
            'func_name' => self::FUNCTION_CREATE,
            'func_param' => [
                'patch_json' => [
                    'message' => (string)$message,
                    'room' => (string)$room,
                    'sender' => (string)$userModel->id,
                    'file_id' => $file
                ]
            ]
        ]);
    }

    public static function delete($id)
    {
        $model = new static();
        return $model->processData([
            'lib_name' => static::$dataLib,
            'func_name' => self::FUNCTION_DELETE,
            'func_param' => [
                'PK' => (string)$id
            ]
        ]);
    }

    public static function _getList($room)
    {
        $result = [];

        $userModel = Yii::$app->session['screenData'][UserAccount::class];
        $userTimeZone = substr($userModel->timezone_code, 4, 6);
        if (!empty($room) && !empty($userModel->id)) {
            $messages = (new static())->processData([
                'lib_name' => static::$dataLib,
                'func_name' => self::FUNCTION_GET_LIST,
                'func_param' => [
                    'user' => (string)$userModel->id,
                    'room' => $room
                ]
            ]);

            $result['messages'] = [];
            $result['rights'] = [];

            if (!empty($messages['record_list']['rights'])) {
                $result['rights'] = $messages['record_list']['rights'];

            }

            if (!empty($messages['record_list']['messages'])) {
                foreach($messages['record_list']['messages'] as $i => $message) {
                    $result['messages'][$i] = $message;
                    $result['messages'][$i]['message_time'] = strtotime($userTimeZone, $message['message_time']);
                }
            }
        }

        return $result;
    }
}
