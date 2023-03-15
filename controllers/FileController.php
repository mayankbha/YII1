<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\controllers;

use app\models\DocumentGroup;
use Yii;
use app\models\FileModel;
use yii\filters\AccessControl;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class FileController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    public function afterAction($action, $result)
    {
        if (Yii::$app->user->isGuest) {
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->redirect(Url::toRoute(['/login']));
            } else {
                $this->redirect(Url::toRoute(['/login']));
            }
            return false;
        }

        return parent::afterAction($action, $result);
    }

    public function actionShow($name = null)
    {
        try {
            $name = str_replace(['/', '\\'], "", $name);
            $filePath = FileModel::getDirectory('@webroot', '/') . '/' . $name;

            if (!file_exists($filePath)) {
                throw new \Exception(Yii::t('errors','No such file or directory'));
            }

            $convertExtension = FileModel::getNeedleExtension($filePath);
            switch ($convertExtension) {
                case FileModel::FILE_TXT_EXTENSION:
                    $pre = Html::tag('pre', $this->renderFile(FileModel::getDirectory('@webroot') . '/' . $name));
                    return Html::tag('body', $pre, ['style' => 'background: #fff']);
                    break;
                default:
                    $convertedFileName = FileModel::convert($filePath, $convertExtension);
                    return $this->redirect([
                        FileModel::getViewerJsPath() . FileModel::getDirectory(null, '/') . '/' . $convertedFileName
                    ]);
                    break;
            }
        } catch (\Exception $e) {
            return $this->renderPartial('error_frame', ['message' => $e->getMessage()]);
        }
    }

    public function actionUpload()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors','Your browser sent a request that this server could not understand'));
        }

        try {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $directory = FileModel::getDirectory('@webroot');
            $fileAttribute = 'file';

            if (!is_dir($directory)) {
                FileHelper::createDirectory($directory);
            }

            if ($file = UploadedFile::getInstanceByName($fileAttribute)) {
                $hash = hash_file('sha256', $file->tempName);
                $fileName = $hash . '.' . $file->extension;
                $filePath = FileHelper::normalizePath($directory . DIRECTORY_SEPARATOR . $fileName);
                if ($file->saveAs($filePath)) {
                    return [
                        'file_name' => $fileName,
                        'original_file_name' => $file->name
                    ];
                }
            }
            throw new \Exception(Yii::t('errors','Can\'t upload file to the server'));
        } catch (\Exception $e) {
            Yii::$app->response->setStatusCode(400);
            return $e->getMessage();
        }
    }

    public function actionDelete()
    {
        $fileName = Yii::$app->request->post('file_name', 'null');
        $fileName = str_replace(['/', '\\'], "", $fileName);

        $directory = FileModel::getDirectory('@webroot');
        $filePath = FileHelper::normalizePath($directory . DIRECTORY_SEPARATOR . $fileName);
        if (is_file($filePath)) {
            unlink($filePath);
        }
    }

    public function actionInitDownload($pk)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors','Your browser sent a request that this server could not understand'));
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            return [
                'status' => 'success',
                'response' => FileModel::initDownloadFile($pk)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function actionDownloadFragment($pk, $file_name, $offset)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors','Your browser sent a request that this server could not understand'));
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $file_name = str_replace(['/', '\\'], "", $file_name);
            $filePath = FileModel::getDirectory('@webroot') . DIRECTORY_SEPARATOR . $file_name;

            if (filesize($filePath) < $offset) {
                return ['status' => 'success'];
            }

            return [
                'status' => 'completed',
                'response' => FileModel::putFileChunk($pk, $filePath, $offset)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function actionFinishDownload($file_name, $file_size, $file_hash)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors','Your browser sent a request that this server could not understand'));
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $file_name = str_replace(['/', '\\'], "", $file_name);
            $file_size = (int)$file_size;

            return [
                'status' => 'success',
                'response' => FileModel::finishDownloadFile($file_name, $file_size, $file_hash)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function actionInitUpload()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors','Your browser sent a request that this server could not understand'));
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $fileName = Yii::$app->request->post('file_name', false);
            $originalFileName = Yii::$app->request->post('original_file_name', false);
            $family = Yii::$app->request->post('family', false);
            $category = Yii::$app->request->post('category', false);

            if (!$fileName || !$originalFileName || !$family || !$category) {
                throw new BadRequestHttpException(Yii::t('errors','Has no required params for upload file'));
            }

            $accessRight = DocumentGroup::getAccessPermission($family, $category);

            if (!$this->isFullAccess($accessRight)) {
                throw new BadRequestHttpException(Yii::t('errors','Access denied'));
            }

            $post['file_name'] = str_replace(['/', '\\'], "", $fileName);

            return [
                'status' => 'success',
                'response' => FileModel::initUploadAsync($fileName, $originalFileName, $family, $category)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function actionUploadFragment()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors','Your browser sent a request that this server could not understand'));
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $pk = Yii::$app->request->post('pk', false);
            $fileName = Yii::$app->request->post('file_name', false);
            $offset = Yii::$app->request->post('offset', null);
            $chunk = Yii::$app->request->post('chunk', null);

            if (!$pk || !$fileName || !isset($chunk) || !isset($offset)) {
                throw new BadRequestHttpException(Yii::t('errors','Has no required params for upload file'));
            }

            $fileName = str_replace(['/', '\\'], "", $fileName);
            $filePath = FileModel::getDirectory('@webroot') . DIRECTORY_SEPARATOR . $fileName;

            if (filesize($filePath) < $offset) {
                return ['status' => 'success'];
            }

            return [
                'status' => 'completed',
                'response' => FileModel::setFileChunk($pk, $filePath, $offset, $chunk)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function actionFinishUpload()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors','Your browser sent a request that this server could not understand'));
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            if (!($pk = Yii::$app->request->post('pk', false))) {
                throw new BadRequestHttpException(Yii::t('errors','Has no required params for  finish upload file'));
            }

            return [
                'status' => 'success',
                'response' => FileModel::finishUploadAsync($pk)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function actionFieldUploadImage()
    {
        try {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $directory = FileModel::getDirectory('@webroot');
            $fileAttribute = 'file';

            if (!is_dir($directory)) {
                FileHelper::createDirectory($directory);
            }

            if ($file = UploadedFile::getInstanceByName($fileAttribute)) {
                $filePath = FileHelper::normalizePath($directory . DIRECTORY_SEPARATOR . $file->name);
                if ($file->saveAs($filePath)) {
                    $img = file_get_contents($filePath); 

                    //Encode the image string data into base64
                    $data = base64_encode($img);

                    unlink($filePath);

                    return [
                        'data' => $data
                    ];
                }
            }
            throw new \Exception("Can't upload file to the server");
        } catch (\Exception $e) {
            Yii::$app->response->setStatusCode(400);
            return $e->getMessage();
        }
    }

    public function actionDocumentUpload()
    {
        try {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $family = Yii::$app->request->post('family', false);
            $category = Yii::$app->request->post('category', false);
            $accessRight = DocumentGroup::getAccessPermission($family, $category);
            if (!$this->isFullAccess($accessRight)) {
                throw new BadRequestHttpException(Yii::t('errors','Access denied'));
            }

            $directory = FileModel::getDirectory('@webroot');
            $fileAttribute = 'file';

            if (!is_dir($directory)) {
                FileHelper::createDirectory($directory);
            }

            if ($file = UploadedFile::getInstanceByName($fileAttribute)) {
                $filePath = FileHelper::normalizePath($directory . DIRECTORY_SEPARATOR . $file->name);

                if ($file->saveAs($filePath)) {
                    return $file;
                }
            }
            throw new \Exception("Can't upload file to the server");
        } catch (BadRequestHttpException $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            Yii::$app->response->setStatusCode(400);
            return $e->getMessage();
        }
    }

    public function actionDocumentInitUpload()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $fileName = Yii::$app->request->post('file_name', false);
            $family = Yii::$app->request->post('family', false);
            $category = Yii::$app->request->post('category', false);
            $description = Yii::$app->request->post('description', false);
            $kps = Yii::$app->request->post('kps', false);

            if (!$fileName || !$family || !$category) {
                throw new BadRequestHttpException('Has no required params for upload file');
            }

            $post['file_name'] = str_replace(['/', '\\'], "", $fileName);

            return [
                'status' => 'success',
                'response' => FileModel::initUploadAsync($fileName, null, $family, $category, $description, $kps)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function actionDocumentUpdateInitUpload()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $filePK = Yii::$app->request->post('filePK', false);

            $fileInfo = FileModel::getFileContainer($filePK);
            if (empty($fileInfo)) {
                throw new \Exception('File not found.');
            }
            $accessRight = DocumentGroup::getAccessPermission($fileInfo['document_family'], $fileInfo['document_category']);

            if(!$this->isFullAccess($accessRight)) {
                throw new \Exception('Not enough rights for this action.');
            }

            return [
                'status' => 'success',
                'response' => FileModel::initUpdateUploadAsync($fileInfo)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function actionDocumentUploadFragment()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors','Your browser sent a request that this server could not understand'));
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $pk = Yii::$app->request->post('pk', false);
            $fileName = Yii::$app->request->post('file_name', false);
            $offset = Yii::$app->request->post('offset', null);
            $chunk = Yii::$app->request->post('chunk', null);

            if (!$pk || !$fileName || !isset($chunk) || !isset($offset)) {
                throw new BadRequestHttpException(Yii::t('errors','Has no required params for upload file'));
            }

            $fileName = str_replace(['/', '\\'], "", $fileName);
            $filePath = FileModel::getDirectory('@webroot') . DIRECTORY_SEPARATOR . $fileName;

            if (filesize($filePath) < $offset) {
                return ['status' => 'success'];
            }

            return [
                'status' => 'completed',
                'response' => FileModel::setFileChunk($pk, $filePath, $offset, $chunk)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function actionDocumentFinishUpload()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors','Your browser sent a request that this server could not understand'));
        }
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            if (!($pk = Yii::$app->request->post('pk', false))) {
                throw new BadRequestHttpException(Yii::t('errors','Has no required params for finish upload file'));
            }

            return [
                'status' => 'success',
                'response' => FileModel::finishUploadAsync($pk)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function actionDocumentDownloadFragment()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors','Your browser sent a request that this server could not understand'));
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $filePK = Yii::$app->request->post('filePK', false);
        $fileName = Yii::$app->request->post('fileName', false);
        $fileSize = Yii::$app->request->post('fileSize', false);
        $offset = Yii::$app->request->post('offset', false);
        $chunk = Yii::$app->request->post('chunk', false);
        $remainingChunk = Yii::$app->request->post('remainingChunk', false);
        $fileData = Yii::$app->request->post('fileData', false);
        $data = json_decode($fileData);
        //$data = base64_decode($fileData);

        if($fileData != '')
            $file_data = $data;
        else
            $file_data = array();

        if($fileSize <= $chunk) {
            $chunk = $fileSize;

            $remainingChunk = 0;
        } else {
            $fileSize = $fileSize - $chunk;

            $remainingChunk = $fileSize;
        }

        $response = FileModel::documentDownloadFragment($filePK, $offset, $chunk);

        if(!empty($response['list'])) {
           array_push($file_data, $response['list']['file_data']);

           //echo base64_decode($response['file_data']);

            //$fileData = base64_decode($response['file_data']);

            /*if($response['list']['file_data'] === base64_encode($response['list']['file_data'])) {
                $decoded = base64_decode($response['list']['file_data'], true);
                $fileData = $fileData . $decoded;
            } else {
                $fileData = $fileData . $response['list']['file_data'];
            }*/

            //echo base64_decode($response['file_data']);

            //for ($i=0; $i < ceil(strlen($response['file_data'])/256); $i++)
               // $fileData = $fileData . base64_decode(substr($response['file_data'],$i*256,256));

            //$fileData = $fileData . mb_convert_encoding(base64_decode($response['file_data']), 'UTF-8', 'UTF-8');
        }

        //echo '<pre>'; print_r($file_data);
        //echo json_encode($file_data);

        /*if($chunk != $remainingChunk) {
            $fileSize = $fileSize - $chunk;
            $remainingChunk = $chunk;
        } else {
            $remainingChunk = 0;
        }*/

        if($remainingChunk != 0)
            $offset = $offset + $chunk;

        return [
            'status' => 'success',
            'remainingChunk' => $remainingChunk,
            'offset' => $offset,
            'chunk' => $chunk,
            'fileSize' => $fileSize,
            'postData' => $response['postData'],
            'fileData' => json_encode($file_data),
            //'url' => 'http://192.168.100.229/codiac/web/file/document-download',
            'response' => $response['list']
        ];
    }

    /*public function actionTest() {
        $file = 'http://192.168.100.229/codiac/web/upload/867f172ea52dd5f1dbe51ef5b90e8fb9/testing.txt';
        $name = 'testing.txt';

        $path = '/upload/867f172ea52dd5f1dbe51ef5b90e8fb9/';
        $file = 'testing.txt';
        $root = Yii::getAlias('@webroot').$path.$file;

        if (file_exists($root)) {
            return Yii::$app->response->sendFile($root);
        } else {
            throw new \yii\web\NotFoundHttpException("{$file} is not found!");
        }
    }*/

    public function actionDocumentDownload()
    {
        //To download file without ajax
        /*$path = '/upload/867f172ea52dd5f1dbe51ef5b90e8fb9/';
        $file = 'testing.txt';
        $root = Yii::getAlias('@webroot').$path.$file;

        if (file_exists($root)) {
            return Yii::$app->response->sendFile($root);
        } else {
            throw new \yii\web\NotFoundHttpException("{$file} is not found!");
        }*/



        //Save file to local system
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors','Your browser sent a request that this server could not understand'));
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $fileData = Yii::$app->request->post('fileData', false);
        $fileData2 = Yii::$app->request->post('fileData2', false);
        $fileName = Yii::$app->request->post('fileName', false);
        $mimeType = Yii::$app->request->post('mimeType', false);

        $file_data = json_decode($fileData2);
        $data = '';

        //echo '<pre>'; print_r();

        if(!empty($file_data)) {
            foreach($file_data as $tmpdata) {
                $data .= base64_decode($tmpdata);
            }
        }

        //echo $data; die;

        /*if($fileData2 == null || $fileData2 == '')
            $data = base64_decode($fileData);
        else
            $data = base64_decode($fileData);*/
            //$data = $fileData2;

        $path = Yii::getAlias('@webroot').'/download/';

        array_map('unlink', array_filter((array) glob("$path*")));

        $fileName = time().'_'.$fileName;
        file_put_contents($path.$fileName, $data);

//        $file = Url::home('http').'download/'.$fileName;
        $file = Url::home().'download/'.$fileName;
        //$file = 'http://192.168.100.229/codiac/web/download/'.$fileName;

        return [
            'status' => 'success',
            'url' => $file
        ];



        /*if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors','Your browser sent a request that this server could not understand'));
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $fileName = Yii::$app->request->post('fileName', false);
        $mimeType = Yii::$app->request->post('mimeType', false);
        $fileData = Yii::$app->request->post('fileData', false);*/

        /*return [
            'status' => 'success',
            'url' => 'http://192.168.100.229/codiac/web/upload/867f172ea52dd5f1dbe51ef5b90e8fb9/testing.txt'
        ];*/

        //$file = 'http://192.168.100.229/codiac/web/upload/867f172ea52dd5f1dbe51ef5b90e8fb9/testing.txt';
        //$name = 'testing.txt';

        //$path = Yii::getAlias('@webroot').'/bukti/'.$download->bukti;

       // if (file_exists($file)) {
            //return Yii::$app->response->sendFile($file);
        //}

        //if (file_exists($file)) {
            /*header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.$name);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
            exit;*/

            /*header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($name).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;*/
        //}

        //header('Content-Description: File Transfer');
        //header('Content-Type: '.$mimeType);
        //header('Content-Disposition: attachment; filename='.$fileName);
        //echo base64_decode($fileData);

        /*-ob_clean();
        flush();*/
        //$file = base64_decode($fileData);
        //$download_name = basename($file);

        // Load GD resource from binary data
        //$im = imageCreateFromString($file);

        /*if(file_exists($im)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.$download_name);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($im));
            ob_clean();
            flush();
            readfile($im);
            exit;
        }*/

        /*header("HTTP/1.1 200 OK");
        header("Pragma: public");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

        // The optional second 'replace' parameter indicates whether the header
        // should replace a previous similar header, or add a second header of
        // the same type. By default it will replace, but if you pass in FALSE
        // as the second argument you can force multiple headers of the same type.
        header("Cache-Control: private", false);

        header("Content-type: " . $mimeType);

        // $strFileName is, of course, the filename of the file being downloaded. 
        // This won't have to be the same name as the actual file.
        header("Content-Disposition: attachment; filename=\"{$fileName}\""); 

        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . mb_strlen($fileData));*/

        // $strFile is a binary representation of the file that is being downloaded.
        //return $fileData;

        /*$out = base64_decode($fileData);
        header('Content-Length: ' . strlen($out));
        ob_end_flush();
        //ob_end_clean();
        echo $out;
        exit;*/

        //header('Content-Type: '.$mimeType);
        //header('Content-Disposition: attachment; filename='.$fileName);

       //echo base64_decode($fileData);

        //echo $image_base64 = base64_decode($fileData);

        //Yii::$app->response->xSendFile($fileData);

        /*ob_start();

        header('Content-Description: File Transfer');
        //header('Content-Type: '.$mimeType);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.$fileName);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($fileData));
        ob_end_flush();
        ob_end_clean();
        flush();
        //echo $fileData;
        readfile($fileData);*/

        /*$fileHash = Yii::$app->request->post('file_hash', false);
        $fileHashHex = bin2hex(base64_decode($fileHash));
        $fileInfo = pathinfo($fileName);
        $fileRoot = DIRECTORY_SEPARATOR . $fileHashHex . '.' . $fileInfo['extension'];
        echo $filePath = FileModel::getDirectory('@webroot') . DIRECTORY_SEPARATOR . $fileName;

        //echo $filePath = FileModel::getDirectory('@webroot') . $fileRoot;

        //$directory = FileModel::getDirectory('@webroot');
        //$filePath = FileHelper::normalizePath($directory . DIRECTORY_SEPARATOR . $fileName);

        echo $filePath;

        if (file_exists($filePath)) { echo 'in file exist if';
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        }*/
    }

    public function actionGetDocumentList()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors','Your browser sent a request that this server could not understand'));
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $kps = Yii::$app->request->post('kp', false);

            //$kps = json_decode($kp);

            //echo '<pre>'; print_r($kps);

            if (!empty($kps)) {
                $document_list = FileModel::getDocumentList($kps);

                return (!empty($document_list)) ? $document_list : [];
            }

            return [
                'status' => 'error',
                'response' => 'Some error occured. Please try again later.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function actionDocumentDelete()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors','Your browser sent a request that this server could not understand'));
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $pk = Yii::$app->request->post('pk', false);

            $fileInfo = FileModel::getFileContainer($pk);
            if (empty($fileInfo)) {
                throw new \Exception('File not found.');
            }
            $documentCategory = DocumentGroup::getAccessPermission($fileInfo['document_family'], $fileInfo['document_category']);

            if(!$this->isFullAccess($documentCategory)) {
                throw new \Exception('Not enough rights for this action.');
            }

            if ($pk != '') {
                $response = FileModel::deleteDocument($pk, 'false');

                return $response;

                //return (!empty($document_list)) ? $document_list : [];
            }

            return [
                'status' => 'error',
                'response' => 'Some error occured. Please try again later.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function actionGetDeletedDocumentList()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors','Your browser sent a request that this server could not understand'));
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $kps = Yii::$app->request->post('kp', false);

            //$kps = json_decode($kp);

            //echo '<pre>'; print_r($kps);

            if (!empty($kps)) {
                $document_list = FileModel::getDocumentList($kps, 'false');

                return (!empty($document_list)) ? $document_list : [];
            }

            return [
                'status' => 'error',
                'response' => 'Some error occured. Please try again later.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function actionDocumentUndelete()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException(Yii::t('errors','Your browser sent a request that this server could not understand'));
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $pk = Yii::$app->request->post('pk', false);

            if ($pk != '') {
                $response = FileModel::deleteDocument($pk, 'true');

                return $response;

                //return (!empty($document_list)) ? $document_list : [];
            }

            return [
                'status' => 'error',
                'response' => 'Some error occured. Please try again later.'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function actionDocumentUpdate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        try {
            $pk = Yii::$app->request->post('pk', false);
            $family = Yii::$app->request->post('family', false);
            $category = Yii::$app->request->post('category', false);
            $description = Yii::$app->request->post('description', false);

            if (!$category) {
                throw new BadRequestHttpException('Has no required params for update document');
            }

            $accessRight = DocumentGroup::getAccessPermission($family, $category);
            if (!$this->isFullAccess($accessRight)) {
                throw new BadRequestHttpException(Yii::t('errors','Access denied'));
            }

            $response = FileModel::updateDocument($pk, $category, $description);

            return $response;
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    public function actionAnnotatePdf()
    {
        $filePK = Yii::$app->request->get('filePK');

        $fileInfo = FileModel::getFileContainer($filePK);
        if (empty($fileInfo)) {
            throw new \Exception('File not found.');
        }
        $documentCategory = DocumentGroup::getAccessPermission($fileInfo['document_family'], $fileInfo['document_category']);

        if(!$this->isFullAccess($documentCategory)) {
            throw new \Exception('Not enough rights for this action.');
        }

		$isAdmin = $documentCategory == DocumentGroup::ACCESS_RIGHT_ADMIN;

        return $this->renderPartial('pdf/annotate', [
            'fileInfo' => $fileInfo,
			'isAdmin' => $isAdmin,
        ]);
    }

    private function isFullAccess($accessRight)
    {
        return $accessRight == DocumentGroup::ACCESS_RIGHT_FULL || $accessRight == DocumentGroup::ACCESS_RIGHT_ADMIN;
    }
}
