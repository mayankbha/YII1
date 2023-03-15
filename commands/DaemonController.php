<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\commands;

use Yii;
use app\models\FileModel;
use yii\console\Controller;
use yii\helpers\FileHelper;

class DaemonController extends Controller
{
    public function actionIndex() {
        echo "Cron service running...\r\n";
    }

    public function actionSessionFilesGc() {
        echo "Start garbage collector for session upload files... \r\n";

        $sessionPrefix = 'sess_';
        if ($uploadDirectory = glob(Yii::getAlias('@app/web') . FileModel::FILE_BASIC_DIRECTORY . '*')) {
            foreach ($uploadDirectory as $uploadSessionDirectory) {
                if(is_dir($uploadSessionDirectory)) {
                    $directoryInfo = pathinfo($uploadSessionDirectory);
                    $sessionFilePath = session_save_path() . DIRECTORY_SEPARATOR . $sessionPrefix . $directoryInfo['basename'];
                    if (!file_exists($sessionFilePath)) {
                        FileHelper::removeDirectory($uploadSessionDirectory);
                        echo 'Remove - ' . $uploadSessionDirectory . "\r\n";
                    }
                }
            }
        }

        echo "Stop garbage collector...\r\n";
    }
}