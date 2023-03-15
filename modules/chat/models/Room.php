<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 **************************************************************************
 Date			Developer				Task ID			Description
 2019/07/25		Mayank Bhatnagar		33				Need to change getList() function name to _getList() as same function is used in BaseController.php so it shows error as here its static but in BaseController.php its not static.
 **************************************************************************
 */
 
namespace app\modules\chat\models;

use app\models\User;
use app\models\UserAccount;
use Yii;

class Room extends BaseModel
{
    const CREATE_ROOM_FUNC = 'CreateRoomCheckAccess';
    const DELETE_ROOM_FUNC = 'DeleteRoomCheckAccess';
    const UPDATE_ROOM_FUNC = 'UpdateRoom';
    const UPDATE_ROOM_USER_FUNC = 'UpdateRoomUser';
    const GET_ROOM_LIST_FUNC = 'GetRoomList';
    const GET_ROOM_LIST_BY_USER_FUNC = 'GetRoomListByUser';
    const GET_USER_LIST_FUNC = 'GetRoomUserListCheckAccess';
    const SEARCH_ROOM_FUNC = 'SearchRoom';

    const ADD_USER_TO_ROOM_FUNC = 'AddUserToRoom';
    const REMOVE_USER_TO_ROOM_FUNC = 'DeleteUserFromRoom';

    const PRIVATE_CHAT_ROOM = 'RoomType.Private';
    const GROUP_CHAT_ROOM = 'RoomType.Group';

    const RIGHT_U = 'U';
    const RIGHT_R = 'R';
    const RIGHT_N = 'N';

    const BOOL_API_TRUE = 'Y';
    const BOOL_API_FALSE = 'N';

    public static function getData($fieldList = [], $postData = [], $additionallyParam = [])
    {
        $postData = [
            'lib_name' => static::$dataLib,
            'func_name' => self::GET_ROOM_LIST_FUNC
        ];
        return parent::getData($fieldList, $postData, $additionallyParam);
    }

    public static function create($roomName)
    {
        $userModel = Yii::$app->session['screenData'][UserAccount::class];

        if (empty($userModel->id) || empty($roomName)) {
            return false;
        }

        $result = (new static())->processData([
            'lib_name' => static::$dataLib,
            'func_name' => self::CREATE_ROOM_FUNC,
            'func_param' => [
                'patch_json' => [
                    'owner' => $userModel->id,
                    'room_name' => $roomName,
                    'room_type' => self::GROUP_CHAT_ROOM
                ]
            ]
        ]);

        if (!empty($result['record_list']['PK'])) {
            return $result['record_list']['PK'];
        }

        return null;
    }

    /**
     * Create one-to-one room and invite users
     * @param $interlocutor
     * @return bool
     */
    public static function createPrivateRoom($interlocutor)
    {
        $userModel = Yii::$app->session['screenData'][UserAccount::class];

        if (empty($userModel->id) || empty($interlocutor)) {
            return false;
        }

        $postData = [
            'lib_name' => User::$dataLib,
            "func_name"=> "GetUserList"
        ];
        $interlocutorUser = User::getData(['id' => [$interlocutor]], $postData)->getOne();

        if (empty($interlocutorUser)) {
            return false;
        }

        return (new static())->processData([
            'lib_name' => static::$dataLib,
            'func_name' => self::CREATE_ROOM_FUNC,
            'func_param' => [
                'user' => $interlocutor,
                'patch_json' => [
                    'owner' => $userModel->id,
                    'room_name' => $interlocutorUser['account_name'],
                    'room_type' => self::PRIVATE_CHAT_ROOM
                ]
            ]
        ]);
    }

    public static function delete($id)
    {
        $userModel = Yii::$app->session['screenData'][UserAccount::class];

        if (empty($id) || empty($userModel->id)) {
            return false;
        }

        $pk = implode(';', [$id, $userModel->id]);

        return (new static())->processData([
            'lib_name' => static::$dataLib,
            'func_name' => self::DELETE_ROOM_FUNC,
            'func_param' => [
                'PK' => (string)$pk,
                'room' => (string)$id,
                'user' => $userModel->id,
            ]
        ]);
    }

    public static function _getList()
    {
        $userModel = Yii::$app->session['screenData'][UserAccount::class];

        if (empty($userModel->id)) {
            return false;
        }

        $rooms = (new static())->processData([
            'lib_name' => static::$dataLib,
            'func_name' => self::GET_ROOM_LIST_BY_USER_FUNC,
            'func_param' => [
                'user' => (string)$userModel->id,
            ]
        ]);

        return !empty($rooms['record_list']) ? $rooms['record_list'] : [];
    }

    public static function addUser($roomID, $userID)
    {
        if (empty($roomID) || empty($userID)) {
            return false;
        }

        $result = (new static())->processData([
            'lib_name' => static::$dataLib,
            'func_name' => self::ADD_USER_TO_ROOM_FUNC,
            'func_param' => [
                'patch_json' => [
                    'room' => $roomID,
                    'user' => $userID,
                    'rights' => self::RIGHT_U,
                    'deleted' => self::BOOL_API_FALSE
                ]
            ]
        ]);

        if (!empty($result['record_list'])) {
            return $result['record_list'];
        }

        return null;
    }

    public static function removeUser($roomID, $userID)
    {
        if (empty($roomID) || empty($userID)) {
            return false;
        }

        $result = (new static())->processData([
            'lib_name' => static::$dataLib,
            'func_name' => self::REMOVE_USER_TO_ROOM_FUNC,
            'func_param' => [
                'patch_json' => [
                    'room' => $roomID,
                    'user' => $userID
                ]
            ]
        ]);

        return (!empty($result['requestresult']) && ($result['requestresult'] == 'successfully')) ? true : false;
    }

    public static function getUserList($id)
    {
        if (empty($id)) {
            return false;
        }


        $result = (new static())->processData([
            'lib_name' => static::$dataLib,
            'func_name' => self::GET_USER_LIST_FUNC,
            'func_param' => [
                'room' => $id,
                'deleted' => 'N'
            ],
        ]);

        return !empty($result['record_list']) ? $result['record_list'] : [];
    }

    public static function updateUserRights($room, $user, $rights)
    {
        if (empty($room) || empty($user) || empty($rights)) {
            return false;
        }


        $result = (new static())->processData([
            'lib_name' => static::$dataLib,
            'func_name' => self::UPDATE_ROOM_USER_FUNC,
            'func_param' => [
                'PK' => "$room;$user",
                'patch_json' => [
                    'rights' => $rights
                ]
            ],
        ]);

        return (!empty($result['requestresult']) && ($result['requestresult'] == 'successfully')) ? true : false;
    }
}
