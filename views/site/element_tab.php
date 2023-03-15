<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @var $this yii\web\View
 * @var $selfTab array
 * @var $mode string
 * @var $id boolean
 * @var $screen_tabs array
 * @var $cache boolean
 * @var $lastFoundData string
 */

use app\components\RenderTabHelper;
use app\models\workflow\Task;

$renderTab = new RenderTabHelper($selfTab);

echo $renderTab->render(compact(
    'cache',
    'mode',
    'id',
    'screen_tabs',
    'lastFoundData',
    'section_to_refresh',
    'section_depth_value',
    'button_action',
    'header_fields',
    'tableSectionFilterArray',
    'inputValues'
));

//echo '<pre> $lastFoundData :: '; print_r($lastFoundData);

//echo key($lastFoundData['id']);

//echo '<pre> $id :: '; print_r($id);

if(!empty($lastFoundData) && $mode == 'insert')
	$taskKey[key($lastFoundData['id'])] = $lastFoundData[key($lastFoundData['id'])];
else if(!empty($id) && is_array($id))
	$taskKey[key($id)] = $id[key($id)];
else
	$taskKey = array();

//echo '<pre> $taskKey :: '; print_r($taskKey);

$screen_tab_template = json_decode(base64_decode($selfTab['screen_tab_template']));

$FlowId = array_unique($selfTab['FlowId']);
$StepId = array_unique($selfTab['StepId']);

//echo '<pre> $FlowId :: '; print_r($FlowId);
//echo '<pre> $StepId :: '; print_r($StepId);

$readonly = 0;

if($mode == 'edit' && $screen_tab_template->template_layout[0]->layout_type == 'TABLE')
	if(isset($screen_tab_template->template_layout[0]->layout_table->readonly) && $screen_tab_template->template_layout[0]->layout_table->readonly == 1)
		$readonly = 1;
?>

<?php if(!empty($FlowId[0])) { ?>
	<?php foreach($FlowId as $key => $flow_id) { ?>
		<?php $tasks = Task::getTaskList($taskKey, $StepId); ?>

		<?php if(!empty($tasks)) { ?>
			<input type="text" class="screen_workflow_current_step_<?php echo $flow_id ?>" value='<?php echo json_encode($tasks, JSON_HEX_APOS); ?>' />
		<?php } ?>
	<?php } ?>
<?php } ?>
		
<div id="search_extra_param" style="display: none;">
	<input type="text" id="layout_type" value="<?php echo $screen_tab_template->template_layout[0]->layout_type; ?>" />
	<input type="text" id="mode" value="<?php echo $mode; ?>" />
	<input type="text" id="readonly" value="<?php echo $readonly; ?>" />
</div>
