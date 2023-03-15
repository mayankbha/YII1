<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\components;

use app\models\Workflow;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\bootstrap\Html;

class StepperWidget extends Widget
{
    public $config = [];
    public $active_id;

    public function init()
    {
        parent::init();
        if (empty($this->config)) {
            throw new InvalidConfigException("You must define the 'config' property for SearchWidget which must be an array.");
        }
    }

    public function run()
    {
		//echo 'in StepperWidget Run'; die;

        $result = [];
        $key = 0;
        $key1 = 0;

        $prev = '';
        $next = '';

        $array_keys = array_keys($this->config);

		//echo '<pre> $this->config :: '; print_r($this->config);

        foreach ($this->config as $id => $screenInfo) {
            if (($tpl = $screenInfo['tpl']) && $tpl->step_screen->enable) {
                $key++;

                if(in_array($id, $array_keys)) {
                    if($key <= (sizeof($this->config) - 1)) {
                        if($key1 == 0) {
                            $prev = $id;
                            $next = $array_keys[$key1+1];
                        } if($next != '') {
                            $prev = $prev;
                            $next = $array_keys[$key1+1];
                        }
                    } else {
                        $next = $id;
                        $prev = $array_keys[$key1-1];
                    }
                }

                $icon = ($tpl->step_screen->icon) ? Html::icon($tpl->step_screen->icon, ['prefix' => 'glyphicon ']) : null;
                $content = ($icon || $tpl->step_screen->text) ? $icon . ' ' . $tpl->step_screen->text : $key;

                $a = Html::a($content, "javascript: void(0)", ['data' => ['id' => $id, 'prev' => $prev, 'next' => $next], 'class' => ($this->active_id == $id) ? 'active' : null]);

                //$a = Html::a($content, "#tab=$id", ['data' => ['id' => $id], 'class' => ($this->active_id == $id) ? 'active' : null]);

                $lockIcon = '';
                if ($key == 1 && $tpl->step_screen->lockable) {
                    $lockIcon = Html::tag('span', Html::icon('glyphicon-lock', ['prefix' => 'glyphicon ']), [
                        'class' => 'stepper--lock',
                        'data' => [
                            'lock-screen-list' => array_keys($this->config),
                            'tid' => $id
                        ],
                        'style' => [
                            'cursor' => 'pointer',
                        ]
                    ]);
                }

                $releaseIcon = '';
                if ($key < count ($this->config) && $tpl->step_screen->release && Workflow::isHead($tpl->step_screen->group)) {
                    $releaseIcon = Html::tag('span', Html::icon('glyphicon-send', ['prefix' => 'glyphicon ']), [
                        'class' => 'stepper--release',
                        'data' => ['tid' => $id]
                    ]);
                }

                $result[] = Html::tag('div', $a . $lockIcon . ' ' . $releaseIcon, ['class' => 'screen-stepper-step screen-stepper-step--' . $tpl->step_screen->shape]);

                $key1++;
            }
        }

		//echo '<pre> $result :: '; print_r($result);

        if ($result) {
            return Html::tag('div', implode('', $result), ['class' => 'screen-stepper']);
        }

        return null;
    }
}