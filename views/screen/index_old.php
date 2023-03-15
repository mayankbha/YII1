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

//echo '<pre>'; print_r($screenList); die;

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
	.btn-breadcrumb .btn {
	  padding:6px 12px 6px 24px;
	}
	.btn-breadcrumb .btn:first-child {
	  padding:6px 6px 6px 10px;
	  border-radius: 20px ​0px 0px 20px;
	}
	.btn-breadcrumb .btn:last-child {
	  padding:6px 18px 6px 24px;
	  border-radius: 0px 20px 20px 0px;
	}

	/** Default button **/
	.btn-breadcrumb .btn.btn-default:not(:last-child):after {
	  border-left: 10px solid #fff;
	}
	.btn-breadcrumb .btn.btn-default:not(:last-child):before {
	  border-left: 10px solid #ccc;
	}
	.btn-breadcrumb .btn.btn-default:hover:not(:last-child):after {
	  border-left: 10px solid #ebebeb;
	}
	.btn-breadcrumb .btn.btn-default:hover:not(:last-child):before {
	  border-left: 10px solid #adadad;
	}

	/** Info button **/
	.btn-breadcrumb .btn.btn-info:after {border-left: 10px solid <?php echo $settings['tab_selected_color']; ?> !important;}
	.btn-breadcrumb .btn.btn-info:hover:after {border-left: 10px solid <?php echo $settings['tab_selected_color']; ?> !important;}
	.btn-breadcrumb .btn.btn-info:hover:before, .btn-breadcrumb .btn.btn-info:before {border-left: 10px solid <?php echo $settings['tab_selected_color']; ?> !important;}
	.btn-breadcrumb .btn.btn-info {background-color: <?php echo $settings['tab_selected_color']; ?> !important; border-color: <?php echo $settings['tab_selected_color']; ?> !important; color: <?php echo $settings['text_color']; ?> !important;}

	
	/* The heart of the matter */
.testimonial-group > .row {
  overflow-x: auto;
  white-space: nowrap;
}
.testimonial-group > .row > .col-xs-4 {
  display: inline-block;
  float: none;
}

/* Decorations */
.col-xs-4 { color: #fff; font-size: 48px; padding-bottom: 20px; padding-top: 18px; }
.col-xs-4:nth-child(3n+1) { background: #c69; }
.col-xs-4:nth-child(3n+2) { background: #9c6; }
.col-xs-4:nth-child(3n+3) { background: #69c; }
</style>

<?php foreach ($screenList as $item) { ?>
	<?php //echo '<pre> $item :: '; print_r($item); ?>
	<?php //echo '$item["id"] :: ' . $item['id']; ?>

	<?php if(isset($item['workflow_tracker_visible']) && $item['workflow_tracker_visible'] == 1) { ?>
		<div class="row common-workflow-tracker-diagram" id="workflow-tracker-diagram-<?php echo $item['id']; ?>" style="margin-top: 60px; margin-right: 0 !important; margin-left: 10px; position: relative; display: none;">
			<!--<div class="container testimonial-group">
			  <div class="row text-center btn-breadcrumb">
				
			  </div>
			</div>-->

			<div class="container1">
				<div class="btn-group btn-breadcrumb"></div>
			</div>

			<!--<div class="container1">
				<ul class="workflow_tracker_ul">
					
				</ul>
			</div>-->
		</div>
	<?php } ?>
<?php } ?>

<div class="cf sub-content-wrapper" <?php if (isset($this->params['layout_without_params'])): ?>style="top: 0;"<?php endif ?>>
    <form class="workflow-task-block" style="display: none">
        <div class="loading-circle"><div></div><div></div><div></div><div></div></div>
        <div class="workflow-task-item form-group" style="display: none;"></div>
        <div class="workflow-task-description" style="display: none;">
            <input type="hidden" name="CreatedBy" class="workflow-task-input workflow-task-input-hidden" value="<?= Yii::$app->getUser()->getId() ?>">
            <input type="hidden" name="Status" class="workflow-task-input workflow-task-input-hidden" value="Assigned">
            <div class="form-group row">
                <div class="col-sm-4">
                    <label>Create date</label>
                    <input type="text" name="CreatedDate" class="form-control workflow-task-input" readonly>
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
        <div class="tab-content" style="min-height: 700px; position: relative;">
            <div class="screen-group-tab tab-pane active">
                <?php if (!empty($tabModel->list)): ?>
                    <?= (in_array($menuType, [UserAccount::MENU_VIEW_LEFT_BAR])) ? $searchBlock : ''; ?>

                    <?= $this->render('element', compact('tabModel')); ?>
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

<script>
	$(document).ready(function() {
		var nav_bar_height = $('.navbar-default').height();
		//var nav_bar_height = $('#navbar-main').height();

		//alert(nav_bar_height);

		if(nav_bar_height != undefined && nav_bar_height != 'undefined' && nav_bar_height > 0) {
			$('.sub-content-wrapper .tab-content:first').css('margin-top', (nav_bar_height + 6));
			//$('.sub-content-wrapper .tab-content .tab-content').css('margin-top', '20px');
		}
	});
</script>

<?= $this->render('common/alert-message-edit-modal'); ?>
<?= $this->render('common/generate-report'); ?>
<?= $this->render('common/screen-modal'); ?>
<?= $this->render('common/document-modal', compact('screenProperty')); ?>
<?= $this->render('common/image-modal'); ?>