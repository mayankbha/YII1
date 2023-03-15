<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @var $this yii\web\View
 * @var $tabModel Screen
 * @var $screenStepMapper array|null
 * @var $hasLoginScreen bool|null
 */
use app\components\RenderTabHelper;
use app\models\UserAccount;
use app\components\StepperWidget;
use app\models\Screen;

$hasActiveTab = 'active';

?>

<?php //echo (in_array(UserAccount::getMenuViewType(), [UserAccount::MENU_VIEW_TWO_LEVEL, UserAccount::MENU_VIEW_LEFT_BAR])) ? '<div style="overflow-x: auto">' : ''; ?>
<div style="overflow-x: auto">

<style>
	.screen-stepper .screen-stepper-step a {
		min-width: 26px;
		font-size: 17px;
		height: 26px;
		line-height: 22px;
	}
</style>

<div class="btn-group nav-right-group" role="group" data-toggle="tab">
	<?php $tab_hidden = 'visible'; ?>

	<?php foreach ($tabModel->list as $item): ?>
		<?php //echo '<pre>'; print_r($item); ?>

		<?php $FlowId = array_unique($item['FlowId']); ?>

		<?php $screenTemplate = Screen::decodeTemplate($item['screen_tab_template']); ?>

		<?php
			/*echo '<pre> $screenTemplate :: '; print_r($screenTemplate);
			echo '<pre> $alias_framework :: '; print_r($screenTemplate['alias_framework']);
			die;*/

			if($screenTemplate['step_screen']['enable'] == true || $screenTemplate['step_screen']['enable'] == 1)
				if(isset($item['tab_visible']) && $item['tab_visible'] == 1)
					$tab_hidden = 'visible';
				else
					$tab_hidden = 'invisible';
		?>

		<input type="hidden" id="screen_step_<?php echo $item['id']; ?>" value="<?php echo (isset($screenTemplate['screen_step']) && !is_array($screenTemplate['screen_step'])) ? $screenTemplate['screen_step'] : null; ?>" />
		<input type="hidden" id="login_screen_<?php echo $item['id']; ?>" value="<?php echo $item['login_screen']; ?>" />
		<input type="hidden" id="refresh_screen_<?php echo $item['id']; ?>" value="<?php if(isset($screenTemplate['refresh_screen'])) echo $screenTemplate['refresh_screen']; ?>" />
		<input type="hidden" id="refresh_screen_time_<?php echo $item['id']; ?>" value="<?php if(isset($screenTemplate['refresh_screen_time'])) echo $screenTemplate['refresh_screen_time']; ?>" />
		<input type="hidden" id="workflow_tracker_viewable_<?php echo $item['id']; ?>" value="<?php if(isset($item['workflow_tracker_visible'])) echo $item['workflow_tracker_visible']; ?>" />

		<?php
			if((isset($item['login_screen']) && $item['login_screen'] != '') || ($item['screen_tab_weight'] == 99))
				$tab_hidden = 'invisible';
			else
				$tab_hidden = 'visible';
		?>

		<button data-screen="<?= $item['screen_name']; ?>" data-target="#<?= $item['screen_name'] . '_' . $item['id']; ?>" data-tab-id="<?= $item['id']; ?>" data-flow-id="<?= empty($FlowId) ? '' : json_encode($FlowId) ?>" data-flow-step-id="<?= empty($item['StepId'][0]) ? '' : json_encode($item['StepId']) ?>" data-lib="<?= $item['screen_lib']; ?>" class="screen-tab btn btn-default <?= $hasActiveTab ?> <?= $tab_hidden ?>" data-toggle="tab" <?= (isset($screenTemplate['alias_framework']['enable']) && $screenTemplate['alias_framework']['enable'] && $screenTemplate['alias_framework']['request_primary_table']) ? 'data-alias-framework="' . $screenTemplate['alias_framework']['request_primary_table'] .'"' : '' ?>><span title="<?= $item['screen_tab_text']; ?>"><?= $item['screen_tab_text']; ?></span></button>

		<?php $hasActiveTab = ''; ?>
	<?php endforeach; ?>
</div>

<?php //echo (in_array(UserAccount::getMenuViewType(), [UserAccount::MENU_VIEW_TWO_LEVEL, UserAccount::MENU_VIEW_LEFT_BAR])) ? '</div>' : ''; ?>
</div>

<style>
	.tt-menu {
		/*position: inherit !important;*/
	}
</style>

<div class="tab-content " style="<?= !empty($hasLoginScreen) ? 'box-shadow: none !important;' : ''?>">
    <?= StepperWidget::widget(['config' => $tabModel->tplData, 'active_id' => !empty($tabModel->list[0]['id']) ? $tabModel->list[0]['id'] : null]) ?>

    <?php $hasActiveTab = 'in active'; ?>

	<?php foreach ($tabModel->list as $key => $item): ?>
		<?php $screenTemplate = Screen::decodeTemplate($item['screen_tab_template'], true); ?>

		<?php $layout_type = Screen::$types[(int)$screenTemplate->layout_type]; ?>

		<div id="<?= "{$item['screen_name']}_{$item['id']}" ?>" data-template-layout-section-row-cnt="<?php echo $layout_type['row_count']; ?>" data-template-layout-section-col-cnt="<?php echo $layout_type['col_count']; ?>" data-section-lib="<?= $item['screen_lib']; ?>" class="tab-pane fade <?= $hasActiveTab ?>">
			<?php
				$renderTab = new RenderTabHelper($item);
				echo $renderTab->renderHeaderSection();
			?>
		</div>

		<?php $hasActiveTab = ''; ?>
	<?php endforeach; ?>
</div>