<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\components;

use Yii;
use app\models\UserAccount;
use yii\bootstrap\Dropdown;
use yii\bootstrap\Nav;
use yii\bootstrap\Html;
use yii\jui\Widget;

class LeftBarNavigationWidget extends Widget
{
    private $userSettings;

    public $items = [];
    public $feature = [];
    public $language = [];

    public function init()
    {
        parent::init();
        $this->userSettings = UserAccount::getSettings();
    }

    public function run()
    {
        $result[] = $this->getLogoBlock();
        $result[] = $this->getUserBlock();
        if (Yii::$app->controller->id == 'chat') {
            $result[] = $this->getNavList();
            $result[] = $this->getChatLeftMenu();
        } else {
            $result[] = Nav::widget(['items' => $this->items, 'options' => ['class' => 'left-position-navbar-menu']]);
        }

        return Html::tag('div', implode('', $result), ['class' => 'left-position-navbar']);
    }

    private function getNavList()
    {
        $result = '<div class="screen-list-menu hidden">';
        $result .= Nav::widget(['items' => $this->items, 'options' => ['class' => 'left-position-navbar-menu']]);
        $result .= '</div>';

        return $result;
    }

    private function getChatLeftMenu()
    {
        return '
            <div class="chat-left-menu">
                <div id="search">
                    <form id="create-room-form">
                        <input type="text" class="typeahead form-control js-search-user" placeholder="' . Yii::t('chat', 'Search contacts') . '...">
                    </form>
                </div>
                <div id="contacts" class="chat-contacts"></div>
                <div id="bottom-bar">
                    <button class="create-room-button" title="' . Yii::t('chat', 'Create room') . '">
                        <i class="glyphicon glyphicon-plus" aria-hidden="true"></i>
                        <span>' . Yii::t('chat', 'Create room') . '</span>
                    </button>
                </div>
            </div>
        ';
    }

    private function getLogoBlock()
    {
        $img = Html::img(UserAccount::getHeaderLogo());
        return Html::tag('div', $img, ['class' => 'left-position-navbar-logo']);
    }

    private function getUserBlock()
    {
        $avatar = '#';
        if (($settings = UserAccount::getSettings()) && !empty($settings->style_template['avatar_body'])) {
            $avatar = "data:image/jpg;base64,{$settings->style_template['avatar_body']}";
        }
        $result[] = Html::img($avatar, ['class' => 'img-circle img-thumbnail']);
        if (!empty($this->userSettings->account_name)) {
            $result[] = Html::tag('div', $this->userSettings->account_name, ['class' => 'username-block']);
        }

        $feature = []; //[$this->getLanguageDropDown()];
        foreach($this->feature as $item) {
            $feature[] = Html::a($item['icon'], $item['url'], !empty($item['linkOptions'])? $item['linkOptions'] : []);
        }

        $result[] = Html::tag('div', implode('', $feature), ['class' => 'feature-block']);

        return Html::tag('div', implode('', $result), ['class' => 'left-position-navbar-user-info', 'data-toggle' => 'left-navbar-icon']);
    }

    private function getLanguageDropDown() {
        $dropDown = Dropdown::widget(['items' => $this->language]);
        $icon = Html::icon('globe', ['aria-hidden' => 'true']);

        $a = Html::a($icon, '', ['data-toggle' => 'dropdown', 'class' => 'dropdown-toggle']);
        return Html::tag('div', $a . $dropDown, ['class' => 'dropdown']);
    }

}