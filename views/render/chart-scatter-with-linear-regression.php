<?php

/* @var $this yii\web\View */
/* @var $dataProvider array */

use dosamigos\chartjs\ChartJs;
use yii\web\JsExpression;

//$this->registerJsFile('/js/chartjs-plugin-regression-master/dist/chartjs-plugin-regression-0.2.1.js');
?>

<?=
ChartJs::widget([
    'type' => 'scatter',
    'options' => [
        'height' => '400',
        'width' => '400',
        'id' => 'breakout_bar_ver_'.rand(),
    ],
    'plugins' =>  new JsExpression('[ChartRegressions]'),
    'clientOptions' => [
        'scales' => [
            'xAxes' => [
                [
                    'type' => 'linear',
                    'position' => 'bottom',
                ],
            ],
            'yAxes' => [
                [
                    'ticks' => [
                        'beginAtZero' => true
                    ]
                ]]
        ],
        'tooltips' => [
            'callbacks' => [
                'label' =>  new JsExpression('function(chart, data) {
                    return common.generateTooltipsLineChart(chart, data);
                }')
            ],
        ],
        'onResize' => new JsExpression('function (chart) {
            common.resizeChart(chart);
        }')
    ],
    'data' => [
        'datasets' =>  $dataProvider
    ]
]);
?>

