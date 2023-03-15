<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 **************************************************************************
 Date			Developer				Task ID			Description
 2019/07/25		Mayank Bhatnagar		33				Need to change getList() function name to _getList() in actionList() function as same function is used in BaseController.php so it shows error as here its static but in BaseController.php its not static.
 **************************************************************************
 */

namespace app\modules\chat\controllers;

use app\modules\chat\models\Message;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class MessageController extends BaseController
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
        $room = Yii::$app->request->post('room', false);
        $message = Yii::$app->request->post('message', false);
        $file = Yii::$app->request->post('file', false);

        if (!$room || !$message) {
            throw new BadRequestHttpException('Invalid Arguments');
        }

        if (Message::create($room, $message, $file)) {
            return ['status' => 'success'];
        }

        return ['status' => 'error'];
    }

    public function actionDelete()
    {
        $id = Yii::$app->request->post('id', false);

        if (!$id) {
            throw new BadRequestHttpException('Invalid Arguments');
        }

        if (Message::delete($id)) {
            return ['status' => 'success'];
        }

        return ['status' => 'error'];
    }

    public function actionList()
    {
        $room = Yii::$app->request->get('room', false);

        if (!$room) {
            throw new BadRequestHttpException('Invalid Arguments');
        }

        $messages = Message::_getList($room);
        if (is_array($messages)) {
            return [
                'status' => 'success',
                'list' => $messages['messages'],
                'rights' => $messages['rights']
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Can\'t get room messages'
        ];
    }
}