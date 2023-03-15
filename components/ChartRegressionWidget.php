<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\components;

use dosamigos\chartjs\ChartJsAsset;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;

class ChartRegressionWidget extends Widget
{
    public $options = [];
    public $clientOptions = [];
    public $data = [];
    public $type;

    public function init()
    {
        parent::init();
        if ($this->type === null) {
            throw new InvalidConfigException("The 'type' option is required");
        }
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
    }

    /**
     * Renders the widget.
     */
    public function run()
    {
        echo Html::tag('canvas', '', $this->options);
        $this->registerClientScript();
    }

    /**
     * Registers the required js files and script to initialize ChartJS plugin
     */
    protected function registerClientScript()
    {
        $id = $this->options['id'];
        $view = $this->getView();
        ChartJsAsset::register($view);

        $config = Json::encode(
            [
                'type' => $this->type,
                'data' => $this->data ?: new JsExpression('{}'),
                'options' => $this->clientOptions ?: new JsExpression('{}')
            ]
        );

        $js = ";var chartJS_{$id} = new Chart($('#{$id}'),{$config});";
        $view->registerJs($js);
    }
}