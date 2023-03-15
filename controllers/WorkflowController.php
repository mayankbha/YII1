<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\controllers;

use app\components\_FormattedHelper;
use app\models\Screen;
use app\models\ScreenList;
use app\models\services\ExtendedInfo;
use app\models\Workflow;
use app\models\workflow\Step;
use app\models\workflow\Task;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class WorkflowController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    public function beforeAction($action)
    {
        if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Only ajax request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    /******************************
     * OLD WORKFLOW FUNCTIONALITY *
     ******************************/

    public function actionRelease()
    {
        $pk = Yii::$app->request->post('pk', false);
        $message = Yii::t('message-area', 'Couldn\'t release record, screen is configured incorrectly');
        $status = SiteController::STATUS_ERROR;

        if (!$pk ) {
            return compact('message', 'status');
        }

        $message = Yii::t('message-area', 'Access denied. Couldn\'t release record for this screen');
        $tplList = Yii::$app->session['tabData']->getTplList();
        foreach($tplList as $tpl) {
            if (!($tpl['tpl']->step_screen->group && Workflow::isHead($tpl['tpl']->step_screen->group))) {
                return compact('message', 'status');
            }
        }

        if (Workflow::updateRecords($pk)) {
            $message = Yii::t('message-area', 'Screen has been released');
            $status = SiteController::STATUS_SUCCESS;
            $messagePool = ExtendedInfo::getInfoList();
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $message = $errorMessage;
        }

        return compact('messagePool', 'message', 'status');
    }

    public function actionLock()
    {
        $pk = Yii::$app->request->post('pk', false);
        $message = Yii::t('message-area', 'Couldn\'t lock workflow record, screen is configured incorrectly');
        $status = SiteController::STATUS_ERROR;

        if ($pk && Workflow::createRecords($pk)) {
            $message = Yii::t('message-area', 'Screen has been locked at workflow');
            $status = SiteController::STATUS_SUCCESS;
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $message = $errorMessage;
        }

        return compact('message', 'status');
    }

    public function actionUnlock()
    {
        $pk = Yii::$app->request->post('pk', false);
        $message = Yii::t('message-area', 'Couldn\'t unlock workflow record, screen is configured incorrectly');
        $status = SiteController::STATUS_ERROR;

        if ($pk && Workflow::deleteRecords($pk)) {
            $message = Yii::t('message-area', 'Screen has been unlocked at workflow');
            $status = SiteController::STATUS_SUCCESS;
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $message = $errorMessage;
        }

        return compact('message', 'status');
    }

    /***********************************
     *  END OLD WORKFLOW FUNCTIONALITY *
     ***********************************/

    public function actionGetTask(array $taskKey = [], array $flowStepId = [])
    {
        $tasks = Task::getData([
            'TaskKey' => [$taskKey],
            'CurrentStepId' => $flowStepId,
            'AssignedToUser' => [Yii::$app->getUser()->getId()],
            'AssignedToGroup' => explode(';', Yii::$app->getUser()->getIdentity()->group_area)
        ]);

        $taskList = $tasks ? $tasks->getList() : [];
        foreach($taskList as $key => $item) {
            $format = new _FormattedHelper();
            $taskList[$key]['CreatedDate'] = $format->run($item['CreatedDate'], _FormattedHelper::DATE_TIME_TEXT_FORMAT);
            $taskList[$key]['DueDate'] = empty($taskList[$key]['DueDate']) ? '' : $format->run($item['DueDate'], _FormattedHelper::DATE_TEXT_FORMAT);
        }

        return $taskList;
    }

    public function actionGetStep(array $stepId = [])
    {
        $step = Step::getData([
            'StepId' => $stepId
        ]);

        return $step ? $step->getList() : [];
    }

    public function actionUpdateTask()
    {
        $post = Yii::$app->getRequest()->post();
        if (!$post) {
            throw new BadRequestHttpException('Has no valid parameters');
        }

        if (!$post['TaskDescription']) {
            throw new BadRequestHttpException('Error create task: task description can\'t be empty');
        }

        if ($post['DueDate']) {
            $format = new _FormattedHelper();
            $post['DueDate'] = $format->revertDateTime($post['DueDate'], _FormattedHelper::getDefaultDateFormat());
        }

        if (!$post['CurrentStepId']) {
            unset($post['CurrentStepId']);
            unset($post['AssignedToGroup']);
            unset($post['AssignedToUser']);
            unset($post['FlowId']);
            unset($post['CurrentStepUuid']);
        }

        $taskId = $post['TaskId'];
        if (Task::updateModel($taskId, $post)) {
            return ['status' => 'success', 'message' => Yii::t('app', 'Task updated successfully')];
        }

        return ['status' => 'error', 'message' => Yii::t('app', 'Error task update')];
    }

    public function actionCreateTask()
    {
        $post = Yii::$app->getRequest()->post();
        if (!$post) {
            throw new BadRequestHttpException('Has no valid parameters');
        }

        if (!$post['TaskDescription']) {
            throw new BadRequestHttpException('Error create task: task description can\'t be empty');
        }

        if ($post['DueDate']) {
            $format = new _FormattedHelper();
            $post['DueDate'] = $format->revertDateTime($post['DueDate'], _FormattedHelper::getDefaultDateFormat());
        }

        if (Task::setModel($post)) {
            return ['status' => 'success', 'message' => Yii::t('app', 'Task created successfully')];
        }

        return ['status' => 'error', 'message' => Yii::t('app', 'Error task create')];
    }

    public function actionGetScreenUrl($screenId, array $currentTaskKey = [])
    {
        $screen = Screen::getData(['id' => [$screenId]]);
        if ($screen && $screen->isSuccess()) {
            $list = $screen->getList();
            $screenName = $list[0]['screen_name'];

            $screen = ScreenList::getData(['screen_name' => [$screenName]]);
            if ($screen && $screen->isSuccess()) {
                $list = $screen->getList();
                $menuName = $list[0]['menu_name'];

                return Url::to(['/screen',
                    'menu' => $menuName,
                    'screen' => $screenName,
                    '#' => http_build_query([
                        'id' => $currentTaskKey,
                        'tab' => $screenId
                    ])
                ]);
            }
        }
    }

	public function actionGetTaskHistory()
    {
		$TaskId = Yii::$app->getRequest()->post('TaskId');

        $taskHistoryList = Task::getTaskHistory($TaskId);

		//echo "<pre>"; print_r($taskHistoryList); die;

		return $taskHistoryList;
	}

	public function actionGetWorkflowJson()
	{
		$workflow_id = Yii::$app->getRequest()->post('workflow_id');

		$workflow_json = Task::getWorkflowJson($workflow_id);

		return $workflow_json;
	}
}
