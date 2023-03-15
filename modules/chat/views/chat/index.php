<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 *
 * @var $this yii\web\View
 * @var $settings array
 */

use app\modules\chat\assets\ChatAsset;
use kartik\typeahead\TypeaheadAsset;
use app\models\UserAccount;
use yii\helpers\Html;

$this->title = Yii::t('chat', 'Chat');
$this->params['breadcrumbs'][] = $this->title;
$securityFilter = UserAccount::getSecurityFilter();
$ownerAvatar = null;
if (!empty($settings->style_template['avatar_body'])) {
    $ownerAvatar = Html::img("data:image/jpg;base64,{$settings->style_template['avatar_body']}", ['class' => 'online', 'style' => 'float: left;']);
}

if ($securityFilter['allow_chat'] == 'Y') {
    $this->registerAssetBundle(ChatAsset::class);
}
$this->registerAssetBundle(TypeaheadAsset::class);

$this->registerCssFile(
    "https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css",
        [
            'integrity' => "sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN",
            'crossorigin' => "anonymous"
        ]
);
?>

<style>
	.soft-codeon {
	  display: -ms-flexbox; /* IE10 */
	  display: flex;
	  width: 100%;
	}

	.icon {
	  padding: 22px;
	  background: linear-gradient(to left, #ced4da, #ced4da);
	  color: white;
	  min-width: 20px;
	  text-align: center;
	  color: #000;
	  border: 2px solid #ddd;
	}
	.soft-field {
	  width: 100%;
	  padding: 10px;
	  outline: none;
	  border: 2px solid #ddd;
	}
	.soft-field:focus {
	  border: 2px solid #ddd;
	}
	.msg-send-btn {
		/*margin-left: 60px;*/
	}
	.attach-file {
		margin-left: 60px;
	}
	.upload-setting-block {
		border-right: 1px dashed #ccc;
	}
</style>

<div class="cf content-wrapper chat-container">
    <div id="frame">
        <div id="sidepanel" class="<?php if(UserAccount::getMenuViewType() == UserAccount::MENU_VIEW_LEFT_BAR): ?>hidden<?php endif; ?>">
            <div id="profile">
                <div class="chat-wrap">
                    <?= $ownerAvatar ?>
                    <p><?= $settings->account_name ?></p>
                </div>
            </div>
			<div id="bottom-bar" style="position: inherit;">
                <button class="create-room-button text-left"
                        title="<?=Yii::t('chat', 'Create room')?>">
                    <i class="glyphicon glyphicon-plus" aria-hidden="true"></i>
                    <span><?=Yii::t('chat', 'Create room')?></span>
                </button>
            </div>
            <div id="search">
                <form id="create-room-form">
                    <label><i class="glyphicon glyphicon-search"></i></label>
                    <input type="text" class="typeahead form-control js-search-user"
                           title="<?=Yii::t('chat', 'Create room')?>"
                           placeholder="<?=Yii::t('chat', 'Search contacts')?>...">
                </form>
            </div>
            <div id="contacts" class="chat-contacts"></div>
        </div>
        <div class="content chat-messages">
            <div class="contact-profile hidden">
                <div class="room-members list-group list-group-horizontal">
                    <div class="room-name"></div>
                    <div class="list-group">
                        <span class="list-group-item add-user-button hidden" title="<?=Yii::t('chat', 'Add user')?>">
                            <i class="glyphicon glyphicon-plus" aria-hidden="true"></i>
                            <span><?=Yii::t('chat', 'Add')?></span>
                        </span>
                        <span class="room-members-wrap"></span>
                    </div>
                </div>
            </div>
			<div class="messages">
                <h3 class="text-center" style="margin-top: 45%;"><?=Yii::t('chat', 'Select room')?></h3>
            </div>
            <div class="message-input hidden">
                <form method="post" id="message-input-form">
                   <div class="chat-wrap soft-codeon">
						<textarea name="message" rows="2" style="width: 75%" placeholder="<?=Yii::t('chat', 'Write your message')?>..." title="'Enter' - sending; 'Shift'+'Enter' - new line"></textarea>

						<button type="submit" class="submi msg-send-btn"><i class="glyphicon glyphicon-send" aria-hidden="true"></i></button>

						<a href="javascript: void(0);" class="attach-file" data-target="#upload-modal" data-toggle="modal"><i class="fa fa-paperclip icon"></i></a>
					</div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->render('@app/modules/chat/views/chat/upload-modal'); ?>