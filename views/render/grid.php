<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

/**
 * @var $this yii\web\View
 * @var $dataProvider \yii\data\ActiveDataProvider
 * @var $columns array
 * @var $isTopOrientation bool
 * @var $pageCount int
 * @var $scrollPixelHeight int
 * @var $functionName string
 * @var $isScrollTable boolean
 * @var $ids array
 * @var $idTable string
 */

use yii\grid\GridView;

$isNeedScroll = $isScrollTable && $isTopOrientation && !empty($limit) && $limit < sizeof($dataProvider->allModels);
?>

<?= GridView::widget([
    'id' => $idTable,
    'dataProvider' => $dataProvider,
	'showFooter' => ((($mode == '' || $mode == null) && $includeColumnFilter == 1) ? true : false),
    'columns' => $columns,
    'layout' => '<div class="table-responsive'  . ($isNeedScroll ? ' is-top-scroll' : '') . '"'
		. ' style="' . (($isNeedScroll && !empty($scrollPixelHeight)) ? 'max-height: ' . $scrollPixelHeight . 'px !important;' : '') . '">{items}</div>',
    'tableOptions' => [
        'class' => 'table table-hover table-bordered common-table-section-class '.$idTable.'_tbl',
		'data-top-orientation' => (int)$isTopOrientation,
		'data-row-num' => $row_num,
		'data-col-num' => $col_num,
		'data-tab-id' => $tab_id,
		'data-search-configuration' => json_encode($search_configuration)
    ],
    'rowOptions' =>  function ($model, $key, $index, $grid) use ($ids, $isTopOrientation) {
        if (isset($ids[$key]) && !$isTopOrientation) {
            return [
                'id' => $ids[$key],
            ];
        }
    },
]);
?>

<input type="hidden" id="<?php echo $idTable; ?>_table_pagination_count" value="<?php echo $limit; ?>" />
<input type="hidden" id="<?php echo $idTable; ?>_table_row_count" value="<?php echo sizeof($dataProvider->allModels); ?>" />
<input type="hidden" id="<?php echo $idTable; ?>_table_update_only" value="<?php echo $updateOnly; ?>" />
<input type="hidden" id="<?php echo $idTable; ?>_table_include_extended_search" value="<?php echo $includeExtendedSearch; ?>" />

<?php if ($pageCount > 1): ?>
    <?php $active = 'class="active"'; ?>
    <nav aria-label="Page navigation" class="sub-pagination">
        <ul class="pagination">
            <li>
                <a href="#" aria-label="<?= Yii::t('app', 'Previous') ?>">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php for ($i = 1; $i <= $pageCount; $i++): ?>
                <li <?= $active ?>><a href="#"><?= $i ?></a></li>
                <?php $active = ''; ?>
            <?php endfor; ?>
            <li>
                <a href="#" aria-label="<?= Yii::t('app', 'Next') ?>">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>