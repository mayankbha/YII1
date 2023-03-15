<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @var $this yii\web\View
 * @var $tabModel Screen
 * @var $taskList array|null
 * @var $stepList array|null
 * @var $screenProperty array
 *
 * @var $menu string
 * @var $screen array
  **************************************************************************
 Date			Developer				Task ID			Description
 2019/08/01		Brad					Workflow		Added code to fix some workflow screen issue and told me to update it on SVN.
 **************************************************************************
 */

use app\components\_FormattedHelper;
use app\models\Screen;
use app\models\UserAccount;
use kartik\date\DatePicker;
use yii\helpers\Url;
use app\components\SearchWidget;

$this->title = $menu;

$searchBlock = ($tabModel && $config = $tabModel->getSearchConfig()) ? SearchWidget::widget(['config' => $config]) : null;
$buttonsType = UserAccount::getButtonsType();
$menuType = UserAccount::getMenuViewType();

$screenList = $tabModel ? $tabModel->getList() : [];

$userSettings = UserAccount::getSettings();

if(!empty($userSettings->style_template))
	$settings = $userSettings->style_template;

//echo '<pre> $searchBlock :: '; print_r($searchBlock);
//echo '<pre> $config :: '; print_r($config);

$hasLoginScreen = false;
foreach ($tabModel->list as $screenParams) {
    if ($screenParams['login_screen']) {
        $hasLoginScreen = true;
        break;
    }
}

?>

<?php if (($buttonsType == UserAccount::BUTTON_TYPE_PLACE_STYLE) || ($buttonsType == UserAccount::BUTTON_TYPE_PLACE)): ?>
    <?= $this->render('common/left-navigation-bar', compact('screen', 'screenProperty')); ?>
<?php endif ?>

<style>
	.btn-breadcrumb .btn:not(:last-child):after {
	  content: " ";
	  display: block;
	  width: 0;
	  height: 0;
	  border-top: 17px solid transparent;
	  border-bottom: 17px solid transparent;
	  border-left: 10px solid white;
	  position: absolute;
	  top: 50%;
	  margin-top: -17px;
	  left: 100%;
	  z-index: 3;
	}
	.btn-breadcrumb .btn:not(:last-child):before {
	  content: " ";
	  display: block;
	  width: 0;
	  height: 0;
	  border-top: 17px solid transparent;
	  border-bottom: 17px solid transparent;
	  border-left: 10px solid rgb(173, 173, 173);
	  position: absolute;
	  top: 50%;
	  margin-top: -17px;
	  margin-left: 1px;
	  left: 100%;
	  z-index: 3;
	}

	/** The Spacing **/
	
	.btn-breadcrumb .btn:first-child {
	  border-radius: 20px 0px 0px 20px;
	}
	.btn-breadcrumb .btn:last-child {
	  border-radius: 0px 20px 20px 0px;
	}

	/** Default button **/
	.btn-breadcrumb .btn.btn-default:not(:last-child):after {
	  border-left: 10px solid #fff;
	}
	.btn-breadcrumb .btn.btn-default:not(:last-child):before {
	  border-left: 10px solid #ccc;
	}

	/** Info button **/
	.btn-breadcrumb .btn.btn-info:after {border-left: 10px solid <?php echo $settings['tab_selected_color']; ?> !important;}
	.btn-breadcrumb .btn.btn-info:after {border-left: 10px solid <?php echo $settings['tab_selected_color']; ?> !important;}
	.btn-breadcrumb .btn.btn-info:before, .btn-breadcrumb .btn.btn-info:before {border-left: 10px solid <?php echo $settings['tab_selected_color']; ?> !important;}
	.btn-breadcrumb .btn.btn-info {background-color: <?php echo $settings['tab_selected_color']; ?> !important; border-color: <?php echo $settings['tab_selected_color']; ?> !important; color: <?php echo $settings['text_color']; ?> !important;}

	.btn-breadcrumb .btn.btn-default {width: 200px; flex-shrink: 0; padding: 7px ​0px 7px 0px;}
	.btn-breadcrumb .btn.btn-info {width: 200px; flex-shrink: 0; padding: 7px ​0px 7px 0px;}

	.btn-breadcrumb .btn-default:hover {color: #333 !important; background-color: #fff !important; border-color: #ccc !important;}
	.btn-breadcrumb .btn-info:hover {background-color: <?php echo $settings['tab_selected_color']; ?> !important; border-color: <?php echo $settings['tab_selected_color']; ?> !important; color: <?php echo $settings['text_color']; ?> !important;}

	.btn-breadcrumb .btn:active {box-shadow: none !important;}

	.container1 {
		width: 100%;
		display: flex;
		overflow-x: auto;
	}
</style>

<?php $workflow_tracker_arr = array(); ?>

<div class="cf sub-content-wrapper" <?php if (isset($this->params['layout_without_params'])): ?>style="top: 0;"<?php endif ?>>
	<?php foreach ($screenList as $item) { ?>
		<?php //echo '<pre> $item :: '; print_r($item); ?>
		<?php //echo '$item["id"] :: ' . $item['id']; ?>

		<?php $FlowId = array_unique($item['FlowId']); ?>
		<?php //echo '<pre> $FlowId :: '; print_r($FlowId); ?>

		<?php //if(isset($item['workflow_tracker_visible']) && $item['workflow_tracker_visible'] == 1) { position: relative; display: none; ?>
			<?php if(!empty($FlowId[0])) { ?>
				<?php foreach($FlowId as $flow_id) { ?>
					<?php if(!in_array($flow_id, $workflow_tracker_arr)) array_push($workflow_tracker_arr, $flow_id); ?>
				<?php } ?>
			<?php } ?>
		<?php //} ?>
	<?php } ?>

	<?php //echo '<pre> :: '; print_r($workflow_tracker_arr); ?>

	<?php if(!empty($workflow_tracker_arr)) { ?>
		<?php foreach($workflow_tracker_arr as $key => $val) { ?>
			<div class="row common-workflow-tracker-diagram" id="workflow-tracker-diagram-<?php echo $flow_id; ?>" style="margin: 0px 0px 16px 0px;">
				<div class="container1">
					<div class="btn-group btn-breadcrumb" style="display: flex; overflow-x: auto; overflow-y: hidden;"></div>
				</div>

				<!--<div class="container1">
					<ul class="workflow_tracker_ul">
						
					</ul>
				</div>-->
			</div>
		<?php } ?>
	<?php } ?>

    <form class="workflow-task-block" style="display: none">
        <div class="loading-circle"><div></div><div></div><div></div><div></div></div>
        <div class="workflow-task-item form-group" style="display: none;"></div>
        <div class="workflow-task-description" style="display: none;">
            <input type="hidden" name="CreatedBy" class="workflow-task-input workflow-task-input-hidden" value="<?= Yii::$app->getUser()->getId() ?>">
            <input type="hidden" name="Status" class="workflow-task-input workflow-task-input-hidden" value="Assigned">
            <div class="form-group row">
                <div class="col-sm-4">
                    <label>Create date</label>
					<?php
						$format = new _FormattedHelper();
						$today = $format->run(date("Y-m-d"), _FormattedHelper::DATE_TIME_TEXT_FORMAT);

						echo DatePicker::widget([
							'name' => 'CreatedDate',
							'type' => DatePicker::TYPE_INPUT,
							'value' => $today,
							'options' => [
								'readonly' => true
							],
							'pluginOptions' => [
								'format' => (new _FormattedHelper())->getFormatDateForPicker(),
								'enableOnReadonly' => false,
								'readonly' => true
							]
						]);
					?>
                    <!--<input type="text" name="CreatedDate" class="form-control workflow-task-input" readonly />-->
                </div>
                <div class="col-sm-4">
                    <label>Due date</label>
                    <?= DatePicker::widget([
                        'name' => "DueDate",
                        'type' => DatePicker::TYPE_COMPONENT_PREPEND,
                        'options' => [
                            'class' => 'workflow-task-input'
                        ],
                        'pluginOptions' => [
                            'autoclose' => true,
                            'format' => (new _FormattedHelper())->getFormatDateForPicker(),
                            'enableOnReadonly' => false
                        ]
                    ]) ?>
                </div>
                <div class="col-sm-4">
                    <label>Custom attribute</label>
                    <input type="text" name="CustomerDefinedID" class="form-control workflow-task-input" >
                </div>
            </div>
            <div class="form-group row">
                <div class="col-sm-4">
                    <label>Route</label>
                    <select name="CurrentStepId" class="form-control workflow-task-input workflow-route-input"></select>
                    <input type="hidden" name="FlowId" class="workflow-flow-id-input">
                    <input type="hidden" name="CurrentStepUuid" class="workflow-flow-uuid-input">
                </div>
                <div class="col-sm-4">
                    <label>Assign to group</label>
                    <select name="AssignedToGroup" class="form-control workflow-task-input workflow-assigned-group-input"></select>
                </div>
                <div class="col-sm-4">
                    <label>Assign to user (Option)</label>
                    <select name="AssignedToUser" class="form-control workflow-task-input workflow-assigned-user-input"></select>
                </div>
            </div>
			<div class="row">
                <div class="col-sm-12">
                    <label>Description</label>
                    <textarea name="TaskDescription" class="form-control workflow-task-input workflow-task-description-input"><?php if(isset($screenList[0]['TaskDescription'][0]) && $screenList[0]['TaskDescription'][0] != '') echo $screenList[0]['TaskDescription'][0]; ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <label>Notes</label>
                    <textarea name="Notes" class="form-control workflow-task-input"></textarea>
                </div>
            </div>
			
			<div class="row">
                <div class="col-sm-12">
					<br>
                    <button class="btn btn-warning pull-right show-task-history-btn" type="button" style="margin-right: 10px; display: block;">Task History</button>
                </div>
            </div>
        </div>
        <button class="btn btn-default pull-right" type="button" onclick="$('.workflow-task-block').hide()">Close task bar</button>
        <button class="btn btn-success pull-right save-route-btn" type="button" style="margin-right: 10px; display: none;">Save and route</button>
        <div class="clearfix"></div>
    </form>

    <div style="position: relative">
        <?= (!in_array($menuType, [UserAccount::MENU_VIEW_LEFT_BAR])) ? $searchBlock : ''; ?>

        <!-- Nav tabs -->
        <?php /*if (!in_array($menuType, [UserAccount::MENU_VIEW_TWO_LEVEL, UserAccount::MENU_VIEW_LEFT_BAR])): ?>
            <div style="overflow-x: auto;">
                <ul class="nav nav-tabs second-menu-level">
                    <?php foreach ($screenList as $item): ?>
                        <?php $hasActiveTab = ($item['screen_name'] == $screen) ? 'class="active"' : ''; ?>
                        <li <?= $hasActiveTab ?>>
                            <a href="<?= Url::toRoute(['index', 'menu' => $menu, 'screen' => isset($item['screen_name']) ? $item['screen_name'] : null]); ?>" title="<?= $item['screen_text'] ?>">
                                <?= $item['screen_text'] ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif*/ ?>

        <?php if (($buttonsType != UserAccount::BUTTON_TYPE_PLACE_STYLE) && ($buttonsType != UserAccount::BUTTON_TYPE_PLACE)): ?>
            <?= $this->render('common/left-navigation-bar', compact('screen', 'screenProperty')); ?>
        <?php endif ?>

        <!-- Tab panes -->
        <div class="tab-content" style="min-height: 700px; position: relative; <?= $hasLoginScreen ? 'box-shadow: none !important;' : ''?>">
            <div class="screen-group-tab tab-pane active">
                <?php if (!empty($tabModel->list)): ?>
                    <?= (in_array($menuType, [UserAccount::MENU_VIEW_LEFT_BAR])) ? $searchBlock : ''; ?>

                    <?= $this->render('element', compact('tabModel', 'hasLoginScreen')); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="task-history-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>

                <h4 class="modal-title task-history-modal-title">Task History</h4>
            </div>

            <div class="modal-body">
				<table class="table table-hover table-bordered" id="task-history-tbl">
					<thead>
						
					</thead>

					<tbody>
						
					</tbody>
				</table>
            </div>
        </div>
    </div>
</div>

<?php
/*
<script>
	$(document).ready(function() {
		//var nav_bar_height = $('.navbar-default').height();
		var nav_bar_height = $('#navbar-main').height();
		var nav_left_group_height = $('.nav-left-group').height();
		var nav_bar_info_place_height = $('.navbar.info-place').height();

		//alert('nav_bar_height :: ' + nav_bar_height);
		//alert('nav_left_group_height :: ' + nav_left_group_height);
		//alert('nav_bar_info_place_height :: '  + nav_bar_info_place_height);

		if(nav_bar_height != undefined && nav_bar_height != 'undefined' && nav_bar_height > 0) {
			<?php if(($menuType == UserAccount::MENU_VIEW_DEFAULT || $menuType == UserAccount::MENU_VIEW_ONE_LEVEL || $menuType == UserAccount::MENU_VIEW_TWO_LEVEL || $menuType == UserAccount::MENU_VIEW_TWO_LEVEL_TAB) && ($buttonsType == UserAccount::BUTTON_TYPE_DEFAULT || $buttonsType == UserAccount::BUTTON_TYPE_STYLE)) { ?>
				$('.sub-content-wrapper').css('margin-top', (nav_bar_height + nav_bar_info_place_height + 20));
			<?php } else if(($menuType == UserAccount::MENU_VIEW_DEFAULT || $menuType == UserAccount::MENU_VIEW_ONE_LEVEL || $menuType == UserAccount::MENU_VIEW_TWO_LEVEL || $menuType == UserAccount::MENU_VIEW_TWO_LEVEL_TAB) && ($buttonsType == UserAccount::BUTTON_TYPE_PLACE_STYLE || $buttonsType == UserAccount::BUTTON_TYPE_PLACE)) { ?>
				$('.sub-content-wrapper').css('margin-top', (nav_bar_height + nav_left_group_height + 20));
			<?php } else { ?>
				//$('.sub-content-wrapper').css('margin-top', (nav_bar_height + 20));
			<?php } ?>

			//$('.sub-content-wrapper .tab-content:first').css('margin-top', (nav_bar_height + 6));
			//$('.sub-content-wrapper .tab-content .tab-content').css('margin-top', '20px');

			<?php if($menuType == UserAccount::MENU_VIEW_DEFAULT && ($buttonsType == UserAccount::BUTTON_TYPE_DEFAULT  || $buttonsType == UserAccount::BUTTON_TYPE_STYLE)) { ?>
				$('.info-place').css('top', (nav_bar_height + 2));
			<?php } else if($menuType == UserAccount::MENU_VIEW_DEFAULT && ($buttonsType == UserAccount::BUTTON_TYPE_PLACE_STYLE || $buttonsType == UserAccount::BUTTON_TYPE_PLACE)) { ?>
				$('.nav-left-group').css('top', (nav_bar_height + 2));
			<?php } ?>
		}

		//console.log('window.innerWidth');
		//console.log(window.innerWidth);

		<?php //if ($menuType == UserAccount::MENU_VIEW_LEFT_BAR && ($buttonsType == UserAccount::BUTTON_TYPE_DEFAULT || $buttonsType == UserAccount::BUTTON_TYPE_STYLE)) { ?>
			//if(window.innerWidth == 1440)
				//$('.search-group').css('margin', '18px 15px 5px 0px');
			//else
				//$('#workflow-tracker-diagram-'+field_id+'-'+flow_item.FlowId+ ' .btn-breadcrumb').css('height', '55px');
		<?php //} ?>
	});
</script>
*/
?>

<?= $this->render('common/alert-message-edit-modal'); ?>
<?= $this->render('common/generate-report'); ?>
<?= $this->render('common/screen-modal'); ?>
<?= $this->render('common/document-modal', compact('screenProperty')); ?>
<?= $this->render('common/image-modal'); ?>