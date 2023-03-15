<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models\workflow;

use app\models\BaseModel;
use Yii;

class Task extends BaseModel
{
    public static $dataLib = 'CodiacSDK.AdminScreens';
    public static $dataAction = 'GetTaskList';

	public static function getTaskList(array $taskKey = [], array $flowStepId = []) {
		$postData = array(
						'lib_name' => 'CodiacSDK.AdminScreens',
						'func_name' => 'GetTaskList',
						'func_param' => array(
							'field_name_list' => array("TaskKey", "CurrentStepId", "AssignedToUser", "AssignedToGroup"),
							'field_value_list' => array("TaskKey" => [$taskKey], "CurrentStepId" => $flowStepId, "AssignedToUser" => [Yii::$app->getUser()->getId()], "AssignedToGroup" => explode(';', Yii::$app->getUser()->getIdentity()->group_area))
						)
					);

        $model = new static();
        $response = $model->processData($postData);

		if($response['requestresult'] == 'successfully')
			return $response['record_list'];
		else
			return $response['extendedinfo'];
	}

	public static function getTaskHistory($TaskId) {
		$postData = array(
						'lib_name' => 'CodiacSDK.AdminScreens',
						'func_name' => 'GetTaskHistoryList',
						'func_param' => array(
							'field_name_list' => array("TaskId"),
							'field_value_list' => array("TaskId" => array("$TaskId"))
						)
					);

        $model = new static();
        $response = $model->processData($postData);

		if($response['requestresult'] == 'successfully')
			return $response['record_list'];
		else
			return $response['extendedinfo'];
	}

	public static function getWorkflowJson($workflow_id) {
		$postData = array(
						'lib_name' => 'CodiacSDK.AdminScreens',
						'func_name' => 'GetFlowList',
						'func_param' => array(
							'field_name_list' => array("FlowId"),
							'field_value_list' => array("FlowId" => array("$workflow_id"))
						)
					);

        $model = new static();

        $response = $model->processData($postData);

		if($response['requestresult'] == 'successfully')
			return $response['record_list'];
		else
			return $response['extendedinfo'];
	}
}