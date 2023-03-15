<?php

namespace app\modules\chat;

use Yii;
use yii\base\Module;

class ChatModule extends Module
{
    public $controllerNamespace = 'app\modules\chat\controllers';
    public $layout = '@app/views/layouts/main.php';
    public $defaultRoute = 'chat/index';
}
