<?php

// comment out the following two lines when deployed to production
//defined('YII_ENV') or define('YII_ENV', 'dev');
//defined('YII_DEBUG') or define('YII_DEBUG', YII_ENV == 'dev');
//if (YII_DEBUG) {
//    set_time_limit(0);
//}

defined('YII_VERSION') or define('YII_VERSION', '0.1');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();
