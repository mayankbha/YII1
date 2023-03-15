<?php

/* @var $this yii\web\View */
/* @var $dataProvider array */

use dosamigos\chartjs\ChartJs;
use yii\web\JsExpression;

?>

<?=
ChartJs::widget([
    'type' => 'horizontalBar',
    'options' => [
        'height' => '400',
        'width' => '400',
        'id' => 'breakout_bar_hor_'.rand(),
    ],
    'clientOptions' => [
        'scales' => [
            'xAxes' => [
                [
                    'ticks' => [
                        'beginAtZero' => true
                    ]
                ]]
        ],
        'legend' => [
            'display' => false
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
        'labels' => $categories,
        'datasets' => $dataProvider
    ]
]);
?>

