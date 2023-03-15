<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

class Workflow extends BaseModel
{
    public static $dataLib = 'CodiacSDK.AdminScreens';
    public static $dataAction = 'GetWorkflowList';

    public static function createRecords($recordPk)
    {
        $model = new static();
        $tplList = Yii::$app->session['tabData']->getTplList();

        foreach ($tplList as $id => $tpl) {
            if ($tpl['tpl']->step_screen->lockable) {
                $request = [
                    'lib_name' => self::$dataLib,
                    'func_name' => 'CreateWorkflow',
                    'func_param' => [
                        'patch_json' => [
                            'user_id' => Yii::$app->getUser()->getId(),
                            'screen_id' => (string)$id,
                            'pk_info' => json_encode($recordPk)
                        ]
                    ]
                ];

                if (!$model->processData($request)) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function deleteRecords($recordPk)
    {
        $model = new static();
        $tplList = Yii::$app->session['tabData']->getTplList();

        foreach ($tplList as $id => $tpl) {
            if ($tpl['tpl']->step_screen->lockable) {
                $request = [
                    'lib_name' => self::$dataLib,
                    'func_name' => 'DeleteWorkflow',
                    'func_param' => [
                        'PK' => $id . ';' . json_encode($recordPk)
                    ]
                ];

                if (!$model->processData($request)) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function updateRecords($recordPk)
    {
        $model = new static();
        $tplList = Yii::$app->session['tabData']->getTplList();

        foreach ($tplList as $id => $tpl) {
            if ($tpl['tpl']->step_screen->lockable) {
                $request = [
                    'lib_name' => self::$dataLib,
                    'func_name' => 'UpdateWorkflow',
                    'func_param' => [
                        'PK' => $id . ';' . json_encode($recordPk),
                        "patch_json" => [
                            "release" => "Y",
                        ]
                    ]
                ];

                if (!empty($tpl['tpl']->screen_extensions['release']['pre']['UpdateWorkflow'])) {
                    $request['func_param']['func_extensions_pre'] = [$tpl['tpl']->screen_extensions['release']['pre']['UpdateWorkflow']];
                }
                if (!empty($tpl['tpl']->screen_extensions['release']['post']['UpdateWorkflow'])) {
                    $request['func_param']['func_extensions_post'] = [$tpl['tpl']->screen_extensions['release']['post']['UpdateWorkflow']];
                }

                if (!$model->processData($request)) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function getInfo($tid, $pk)
    {
        $released = false;
        $workflow = false;
        $locked = false;
        $afterReleaseRequired = false;
        $head = false;

        $tpl = Yii::$app->session['tabData']->getSelectTpl();
        if ($tpl['tpl']->step_screen->enable && $tpl['tpl']->step_screen->lockable) {
            $userId = Yii::$app->getUser()->getId();
            $workflowObject = self::getData([
                'screen_id' => [$tid],
                'pk_info' => [$pk]
            ]);

            $head = self::isHead($tpl['tpl']->step_screen->group);
            if ($workflow = ($workflowObject && !empty($workflowObject->list))) {
                $owner = $workflowObject->list[0]['user_id'] == $userId;
                $released = $workflowObject->list[0]['release'] == 'Y';
                $locked = !$owner && !$head;
            }

            if (!$locked) {
                $afterReleaseRequired = self::isScreenAfterReleaseRequired($tid);
                $locked = self::isScreenAfterReleaseRequired($tid) && !$released;
            }
        }

        return compact('locked', 'released', 'workflow','head', 'afterReleaseRequired');
    }

    public static function isHead($group)
    {
        $settings = UserAccount::getSettings();
        return in_array($group, explode(';', $settings->group_area));
    }

    public static function isScreenAfterReleaseRequired($screenId)
    {
        $tabData = Yii::$app->session['tabData'];
        $isNextItemsReleaseRequired = false;

        foreach ($tabData->list as $screen) {
            if (!$isNextItemsReleaseRequired && !empty($tabData->tplData[$screen['id']]['tpl']->step_screen->release)) {
                $isNextItemsReleaseRequired = true;
                continue;
            }

            if ($isNextItemsReleaseRequired && $screenId == $screen['id']) {
                return true;
            }
        }

        return false;
    }
}