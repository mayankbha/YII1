<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
  **************************************************************************
 Date			Developer				Task ID			Description
 2019/07/25		Mayank Bhatnagar		33				Need to change getList() function name to _getList() in actionList() function as same function is used in BaseController.php so it shows error as here its static but in BaseController.php its not static. Also need to update actionCreate() as we need to able to add user as we create room.
 **************************************************************************
 */

namespace app\modules\chat\controllers;

use app\models\UserAccount;
use app\modules\chat\models\Room;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use Yii;

class RoomController extends BaseController
{
    public function beforeAction($action)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Your browser sent a request that this server could not understand');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    public function actionCreate()
    {
        $name = Yii::$app->request->post('room_name', false);
        $user_ids = Yii::$app->request->post('user_ids', false);

        if (!$name) {
            throw new BadRequestHttpException('Invalid Arguments');
        }

        if ($pk = Room::create($name)) {
			if(!empty($user_ids)) {
				foreach($user_ids as $user_id) {
					if ($roomInfo = Room::getData(['owner' => [Yii::$app->getUser()->getId()], 'room' => [$pk]])) {
						Room::addUser($pk, $user_id);
					}
				}
			}
            return [
                'status' => 'success',
                'pk' => $pk
            ];
        }

        return ['status' => 'error'];
    }

    public function actionDelete()
    {
        $id = Yii::$app->request->post('id', null);

        if (!$id) {
            throw new BadRequestHttpException('Invalid Arguments');
        }

        if (Room::delete($id)) {
            return ['status' => 'success'];
        }

        return ['status' => 'error'];
    }

    public function actionList()
    {
        return [
            'status' => 'success',
            'list' => Room::_getList()
        ];
    }

    public function actionAddUser()
    {
        $roomID = Yii::$app->request->post('room_id', null);
        $userID = Yii::$app->request->post('user_id', null);

        if (!$roomID || !$userID) {
            throw new BadRequestHttpException('Invalid Arguments');
        }

        if ($roomInfo = Room::getData(['owner' => [Yii::$app->getUser()->getId()], 'room' => [$roomID]])) {
            if (Room::addUser($roomID, $userID)) {
                return ['status' => 'success'];
            }
        }

        return ['status' => 'error'];
    }

    public function actionInviteUser()
    {
        $interlocutor = Yii::$app->request->post('user_id', null);

        if (!$interlocutor) {
            throw new BadRequestHttpException('Invalid Arguments');
        }

        $privateRoom = Room::createPrivateRoom($interlocutor);
        if (!empty($privateRoom)) {
            return ['status' => 'success'];
        }

        return ['status' => 'error'];
    }

    public function actionRemoveUser()
    {
        $roomID = Yii::$app->request->post('room_id', null);
        $userID = Yii::$app->request->post('user_id', null);

        if (!$roomID || !$userID) {
            throw new BadRequestHttpException('Invalid Arguments');
        }

        if ($roomInfo = Room::getData(['owner' => [Yii::$app->getUser()->getId()], 'room' => [$roomID]])) {
            if (Room::removeUser($roomID, $userID)) {
                return ['status' => 'success'];
            }
        }

        return ['status' => 'error'];
    }

    public function actionUserList()
    {
        $id = Yii::$app->request->post('room', null);

        return [
            'status' => 'success',
            'list' => Room::getUserList($id)
        ];
    }

    public function actionUpdateRights()
    {
        $id = Yii::$app->request->post('room', null);
        $rights = Yii::$app->request->post('rights', null);
        $user = Yii::$app->request->post('user', null);

        if (!$id || !$rights || !$user) {
            throw new BadRequestHttpException('Invalid Arguments');
        }

        if ($roomInfo = Room::getData(['owner' => [Yii::$app->getUser()->getId()], 'room' => [$id]])) {
            if (Room::updateUserRights($id, $user, $rights)) {
                return ['status' => 'success'];
            }
        }

        return ['status' => 'error'];
    }
}