<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\modules\chat\assets;

use Yii;
use yii\bootstrap\BootstrapAsset;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\AssetBundle;
use yii\web\YiiAsset;

class ChatAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'css/chat.css',
    ];

    public $js = [
        'js/chat.js',
    ];

    public $depends = [
        YiiAsset::class,
        BootstrapAsset::class,
    ];

    public function registerAssetFiles($view)
    {
        $user = Yii::$app->getUser()->getIdentity();
        $interval = !empty($user->ChatSettings['refreshInterval']) ? (int)$user->ChatSettings['refreshInterval'] * 1000 : 5000;

        $urls = Json::encode([
            'common' => [
                'interval' => $interval,
            ],
            'user' => Yii::$app->getUser()->getId(),
            'userList' => Url::toRoute('chat/get-user-list'),
            'message' => [
                'create' => Url::toRoute('message/create'),
                'delete' => Url::toRoute('message/delete'),
                'get' => Url::toRoute('message/list')
            ],
            'room' => [
                'create' => Url::toRoute('room/create'),
                'delete' => Url::toRoute('room/delete'),
                'get' => Url::toRoute('room/list'),
                'addUser' => Url::toRoute('room/add-user'),
                'inviteUser' => Url::toRoute('room/invite-user'),
                'removeUser' => Url::toRoute('room/remove-user'),
                'userList' =>  Url::toRoute('room/user-list'),
                'updateRights' => Url::toRoute('room/update-rights')
            ],
            'file' => [
                'uploadInitUrl' => Url::toRoute(['/file/init-upload']),
                'uploadFragmentUrl' => Url::toRoute(['/file/upload-fragment']),
                'uploadFinishUrl' => Url::toRoute(['/file/finish-upload']),
                'downloadInitUrl' => Url::toRoute(['/file/init-download']),
                'downloadFragmentUrl' => Url::toRoute(['/file/download-fragment']),
                'downloadFileFinish' => Url::toRoute(['/file/finish-download'])
            ]
        ]);

        $view->registerJs("ChatObject($urls);");

        parent::registerAssetFiles($view);
    }
}
