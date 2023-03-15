<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @var $this yii\web\View
 * @var $screen string|null
 * @var $screenList array|null
 * @var $screenProperty array|null
 **************************************************************************
 Date			Developer				Task ID			Description
 2019/07/25		Mayank Bhatnagar		33				Added code to open chat in new window.
 **************************************************************************
 */

use yii\helpers\Url;
use app\models\UserAccount;

$menuType = UserAccount::getMenuViewType();
$buttonsType = UserAccount::getButtonsType();

$menuTooltip = UserAccount::getMenuTooltip();

//echo '<pre> $menuTooltip :: '; print_r($menuTooltip);

?>
<!-- Left buttons -->
<div class="nav-left-group" role="group" <?php if (isset($this->params['layout_without_params']) && !in_array($buttonsType, [UserAccount::BUTTON_TYPE_DEFAULT, UserAccount::BUTTON_TYPE_STYLE])): ?>style="left: 0;"<?php endif ?>>
    <?php if (empty($this->params['layout_without_params'])): ?>
        <?php if($menuType == UserAccount::MENU_VIEW_LEFT_BAR && !in_array($buttonsType, [UserAccount::BUTTON_TYPE_DEFAULT, UserAccount::BUTTON_TYPE_STYLE])): ?>
            <button type="button" class="btn btn-default left-navbar-icon glyphicon glyphicon-menu-hamburger"></button>
        <?php endif ?>
    <?php endif ?>

    <button class="btn btn-link workflow-task-btn" href="#" data-target="task" onclick="$('.workflow-task-block').addClass('active').show()" style="display: none">
        <span class="codiacicon codiacicon-assigned size-1em"></span>
        <span class="workflow-task-notification">0</span>
    </button>

    <?php if (isset($screenProperty['add']) && $screenProperty['add'] == "Y"): ?>
        <a class="btn btn-link screen-insert-btn <?= (isset($screenProperty['add_show']) && $screenProperty['add_show'] == "Y") ? '' : 'hidden' ?>" href="#" data-mode="insert" title="<?php echo $menuTooltip['insert']; ?>">
            <span class="codiacicon codiacicon-file size-1em"></span>
        </a>
        <div class="special-sub-btns special-sub-btns-insert <?= (isset($screenProperty['add_show']) && $screenProperty['add_show'] == "Y") ? '' : 'hidden' ?>">
            <a class="btn btn-link left-navigation-button-save" data-action="<?= Url::toRoute(['/site/create-data']); ?>" href="#" title="<?php echo $menuTooltip['save']; ?>">
                <span class="codiacicon codiacicon-save size-1em"></span>
            </a>
            <a class="btn btn-link left-navigation-button-cancel" href="#" data-mode="empty" title="<?php echo $menuTooltip['cancel']; ?>" >
                <span class="codiacicon codiacicon-close size-1em"></span>
            </a>
        </div>
    <?php endif; ?>
    <a class="btn btn-link <?= (isset($screenProperty['inquire_show']) && $screenProperty['inquire_show'] == "Y") ? '' : 'hidden' ?>" href="#" data-mode="key" title="<?php echo $menuTooltip['load']; ?>">
        <span class="codiacicon codiacicon-import size-1em"></span>
    </a>
    <div class="special-btns">
        <?php if (isset($screenProperty['inquire']) && $screenProperty['inquire'] == "Y"): ?>
            <a class="btn btn-link navigation-btn prev <?= (isset($screenProperty['inquire_show']) && $screenProperty['inquire_show'] == "Y") ? '' : 'hidden' ?>" href="#" title="<?php echo $menuTooltip['previous']; ?>" >
                <span class="codiacicon codiacicon-previous size-1em"></span>
            </a>
            <a class="btn btn-link navigation-btn next <?= (isset($screenProperty['inquire_show']) && $screenProperty['inquire_show'] == "Y") ? '' : 'hidden' ?>" href="#" title="<?php echo $menuTooltip['next']; ?>" >
                <span class="codiacicon codiacicon-next size-1em"></span>
            </a>
        <?php endif; ?>
        <?php if (isset($screenProperty['edit']) && $screenProperty['edit'] == "Y"): ?>
            <a href="#" class="btn btn-link screen-edit-btn <?= (isset($screenProperty['edit_show']) && $screenProperty['edit_show'] == "Y") ? '' : 'hidden' ?>" data-mode="edit" title="<?php echo $menuTooltip['edit']; ?>" >
                <span class="codiacicon codiacicon-edit size-1em"></span>
            </a>
        <?php endif; ?>
        <?php if (isset($screenProperty['copy']) && $screenProperty['copy'] == "Y"): ?>
            <a href="#" class="btn btn-link screen-copy-btn <?= (isset($screenProperty['copy_show']) && $screenProperty['copy_show'] == "Y") ? '' : 'hidden' ?>" data-mode="copy" title="<?php echo $menuTooltip['copy']; ?>" >
                <span class="codiacicon codiacicon-copy size-1em"></span>
            </a>
        <?php endif; ?>
        <?php if (isset($screenProperty['delete']) && $screenProperty['delete'] == "Y"): ?>
            <a href="#" class="btn btn-link screen-remove-btn <?= (isset($screenProperty['delete_show']) && $screenProperty['delete_show'] == "Y") ? '' : 'hidden' ?>" data-mode="delete" data-action="<?= Url::toRoute(['/site/delete-data']); ?>" title="<?php echo $menuTooltip['delete']; ?>">
                <span class="codiacicon codiacicon-trash size-1em"></span>
            </a>
        <?php endif ?>
        <?php if (isset($screenProperty['execute']) && $screenProperty['execute'] == "Y"): ?>
            <a href="#" class="btn btn-link screen-execute-btn <?= (isset($screenProperty['execute_show']) && $screenProperty['execute_show'] == "Y") ? '' : 'hidden' ?>" data-mode="execute" title="<?php echo $menuTooltip['execute']; ?>">
				<span class="codiacicon codiacicon-setting size-1em"></span>
            </a>
        <?php endif ?>

        <!-- SPECIAL BUTTONS -->
        <?php if (isset($screenProperty['edit']) && $screenProperty['edit'] == "Y"): ?>
            <div class="special-sub-btns special-sub-btns-edit <?= (isset($screenProperty['edit_show']) && $screenProperty['edit_show'] == "Y") ? '' : 'hidden' ?>">
                <a class="btn btn-link left-navigation-button-save" href="#" data-action="<?= Url::toRoute(['/site/save-data']); ?>" title="<?php echo $menuTooltip['save']; ?>">
                    <span class="codiacicon codiacicon-save size-1em"></span>
                </a>
                <a class="btn btn-link left-navigation-button-cancel" href="#" data-mode="unlock" title="<?php echo $menuTooltip['cancel']; ?>">
                    <span class="codiacicon codiacicon-close size-1em"></span>
                </a>
            </div>
        <?php endif; ?>
        <?php if (isset($screenProperty['copy']) && $screenProperty['copy'] == "Y"): ?>
            <div class="special-sub-btns special-sub-btns-copy <?= (isset($screenProperty['copy_show']) && $screenProperty['copy_show'] == "Y") ? '' : 'hidden' ?>">
                <a class="btn btn-link left-navigation-button-save" href="#" data-action="<?= Url::toRoute(['/site/create-data']); ?>" title="<?php echo $menuTooltip['save']; ?>">
                    <span class="codiacicon codiacicon-save size-1em"></span>
                </a>
                <a class="btn btn-link left-navigation-button-cancel" href="#" data-mode="unlock" title="<?php echo $menuTooltip['cancel']; ?>">
                    <span class="codiacicon codiacicon-close size-1em"></span>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php if (($buttonsType == UserAccount::BUTTON_TYPE_PLACE_STYLE) || ($buttonsType == UserAccount::BUTTON_TYPE_PLACE)): ?>
        <?= $this->render('@app/views/screen/common/message-area'); ?>
    <?php endif ?>
</div>