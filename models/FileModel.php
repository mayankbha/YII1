<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models;

use app\models\services\ExtendedInfo;
use COM;
use Exception;
use Yii;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class FileModel extends BaseModel
{
    const FILE_BASIC_DIRECTORY = '/upload/';

    const FILE_PDF_EXTENSION = 'pdf';
    const FILE_DOC_EXTENSION = 'odt';
    const FILE_XLS_EXTENSION = 'ods';
    const FILE_PPT_EXTENSION = 'odp';
    const FILE_TXT_EXTENSION = 'txt';

    const UPLOAD_STATUS_PREPARING = 'preparing';
    const UPLOAD_STATUS_ALLOCATED = 'allocated';
    const UPLOAD_STATUS_FOR_DELETE = 'for_delete';
    const UPLOAD_STATUS_IN_PROGRESS = 'in_progress';
    const UPLOAD_STATUS_COMPLETED = 'completed';

    const ASYNC_FUNC_INIT = 'Init';

    const CHUNK_SIZE = 51200;

    public static $dataLib = 'CodiacSDK.FileProcessor';

    public static $fileConvertExtensions = [
        self::FILE_DOC_EXTENSION => ['doc', 'docx'],
        self::FILE_XLS_EXTENSION => ['ppt', 'pptx'],
        self::FILE_PPT_EXTENSION => ['xls', 'xlsx']
    ];

    public static function getDirectory($alias = '@web', $separator = DIRECTORY_SEPARATOR)
    {
        $path = Yii::getAlias($alias . self::FILE_BASIC_DIRECTORY) . $separator . Yii::$app->session->id;
        return FileHelper::normalizePath($path, $separator);
    }

    public static function encodeFileName($library, $fieldName)
    {
        return md5($library . '_' . $fieldName);
    }

    public static function getViewerJsPath()
    {
        return Yii::getAlias('/ViewerJS/#..');
    }

    public static function convert($filePath, $convertExtension = self::FILE_PDF_EXTENSION)
    {
        if (!file_exists($filePath)) {
            throw new Exception(Yii::t('errors','No such file or directory'));
        }

        $pathParts = pathinfo($filePath);
        $convertFileName = $pathParts['filename'] . '.' . $convertExtension;
        $convertFilePath = $pathParts['dirname'] . '/' . $convertFileName;

        if (is_file($convertFilePath) || $convertFileName == $pathParts['filename']) {
            return $convertFileName;
        }

        $inputFile = "file:///" . $filePath;
        $outputFile = "file:///" . $convertFilePath;

        if (self::convert2file($inputFile, $outputFile, $convertExtension)) {
            return $convertFileName;
        }

        throw new Exception('Unsuccessfully converted file');
    }

    public static function MakePropertyValue2PdfConvert($name, $value, $osm)
    {
        $oStruct = $osm->Bridge_GetStruct("com.sun.star.beans.PropertyValue");
        $oStruct->Name = $name;
        $oStruct->Value = $value;

        return $oStruct;
    }

    public static function convert2file($inputPath, $outputPath, $convertExtension)
    {
        try {
            $osm = new COM("com.sun.star.ServiceManager");
        } catch (Exception $e) {
            throw new Exception('Please be sure that OpenOffice.org is installed and configured');
        }

        $args = [self::MakePropertyValue2PdfConvert("Hidden", true, $osm)];

        $oDesktop = $osm->createInstance("com.sun.star.frame.Desktop");
        $oWriterDoc = $oDesktop->loadComponentFromURL($inputPath, "_blank", 0, $args);
        $exportArgs = ($convertExtension == self::FILE_PDF_EXTENSION) ? [
            self::MakePropertyValue2PdfConvert("FilterName", "writer_ods_Export", $osm)
        ] : $args;

        $oWriterDoc->storeToURL($outputPath, $exportArgs);
        $oWriterDoc->close(true);

        return true;
    }

    public static function getNeedleExtension($filePath)
    {
        if (!file_exists($filePath)) {
            throw new Exception(Yii::t('errors','No such file or directory'));
        }

        $haystack = ($mime = FileHelper::getMimeType($filePath)) ? FileHelper::getExtensionsByMimeType($mime) : [];
        if (in_array(self::FILE_TXT_EXTENSION, $haystack)) {
            return self::FILE_TXT_EXTENSION;
        }

        foreach (self::$fileConvertExtensions as $const => $convertExt) {
            foreach ($convertExt as $needle) {
                if (in_array($needle, $haystack)) {
                    return $const;
                }
            }
        }

        return self::FILE_PDF_EXTENSION;
    }

    public static function getFileInfo($fileName, $originalFileName = null, $family, $category, $description)
    {
        $directory = self::getDirectory('@webroot');
        $filePath = $directory . DIRECTORY_SEPARATOR . $fileName;

        if (file_exists($filePath) && is_readable($filePath)) {
            return [
                'chunk_size' => (string)self::CHUNK_SIZE,
                'original_file_attributes' => '32',
                'original_file_hash' => base64_encode(hash_file('sha256', $filePath, true)),
                'original_file_name' => ($originalFileName) ? $originalFileName : $fileName,
                'original_file_size' => (string)filesize($filePath),
                "document_category" => $category,
                "document_family" => $family,
                "Description" => $description
            ];
        }

        throw new NotFoundHttpException(Yii::t('errors','File {filename} is not found in the directory for this user', ['filename' => $fileName]));
    }

    public static function initUploadAsync($fileName, $originalFileName = null, $family, $category, $description="", $kps="")
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $message = Yii::t('errors','Can\'t start file upload function');
        if ($fileInfo = self::getFileInfo($fileName, $originalFileName, $family, $category, $description, $kps)) {
            if($kps != '') {
                $kps = json_decode($kps);

                //echo '<pre>'; print_r($kps);

                if(!empty($kps)) {
                    foreach($kps as $key => $kp) {
                        $fileInfo[$key] = $kp[0];
                    }
                }
            }

            //echo '<pre>'; print_r($fileInfo); die;

            $postData = [
                'func_name' => 'Upload_Init_Async',
                'func_param' => [
                    'patch_json' => $fileInfo
                ],
                'lib_name' => self::$dataLib
            ];

            $model = new static();
            if (($result = $model->processData($postData)) && !empty($result['record_list'])) {
                return $result['record_list'];
            } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
                $message = $errorMessage;
            }
        }

        throw new Exception($message);
    }

    public static function initUpdateUploadAsync($fileInfo)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $message = Yii::t('errors','Can\'t start file upload function');

        $directory = self::getDirectory('@webroot');
        $filePath = $directory . DIRECTORY_SEPARATOR . $fileInfo['original_file_name'];

        if (file_exists($filePath) && is_readable($filePath)) {
            $fileInfo['original_file_hash'] = base64_encode(hash_file('sha256', $filePath, true));
            $fileInfo['original_file_size'] = (string)filesize($filePath);
        } else {
            throw new NotFoundHttpException(Yii::t('errors','File {filename} is not found in the directory for this user', ['filename' => $fileName]));
        }

        try {
            self::deleteDocument($fileInfo['id'], $fileInfo['Active']);
        } catch (Exception $e) {
            // var_dump($e->getMessage());die();
        }

        $postData = [
            'func_name' => 'Upload_Init_Async',
            'func_param' => [
                'patch_json' => $fileInfo
            ],
            'lib_name' => self::$dataLib
        ];

        $model = new static();
        $result = $model->processData($postData);
        if (!empty($result) && !empty($result['record_list'])) {
            return $result['record_list'];
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $message = $errorMessage;
        }

        throw new Exception($message);
    }

    public static function uploadFileChunk($pk, $chunkData, $chunkNum)
    {
        $postData = [
            'func_name' => 'Upload_NextChunk_Async',
            'func_param' => [
                'PK' => (string)$pk,
                'patch_json' => [
                    'chunk_data' => base64_encode($chunkData),
                    'chunk_num' => $chunkNum
                ]
            ],
            'lib_name' => self::$dataLib
        ];

        $info = '(not set)';
        $model = new static();
        if (($result = $model->processData($postData)) && !empty($result['record_list'])) {
            return $result['record_list'];
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $info = $errorMessage;
        }

        throw new Exception(Yii::t('errors','Can\'t upload chunk of file at number {chunk_num}: {info}', ['chunk_num' => $chunkNum, 'info' => $info]));
    }

    public static function finishUploadAsync($pk)
    {
        $postData = [
            'func_name' => 'Upload_Finish_Async',
            'func_param' => [
                'PK' => (string)$pk,
            ],
            'lib_name' => self::$dataLib
        ];

        $info = '(not set)';
        $model = new static();
        if (($result = $model->processData($postData)) && !empty($result['record_list'])) {
            return true; 
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $info = $errorMessage;
        }

        try {
            self::deleteUploadAsync($pk);
            throw new Exception();
        } catch (Exception $e) {
            throw new Exception(Yii::t('errors','Can\'t finish upload file: {info}', ['info' => $info]));
        }
    }

    public static function deleteUploadAsync($pk)
    {
        $postData = [
            'func_name' => 'Upload_Delete_Async',
            'func_param' => [
                'PK' => (string)$pk,
            ],
            'lib_name' => self::$dataLib
        ];

        $info = '(not set)';
        $model = new static();
        if (($result = $model->processData($postData)) && !empty($result['record_list'])) {
            return true;
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $info = $errorMessage;
        }

        throw new Exception(Yii::t('errors','Can\'t delete uploaded file from API server: {info}', ['info' => $info]));
    }

    public static function setFileChunk($pk, $filePath, $offset = 0, $chunk = 0)
    {
        if ($fragment = file_get_contents($filePath, NULL, NULL, $offset, self::CHUNK_SIZE)) {
            if (self::uploadFileChunk($pk, $fragment, $chunk)) {
                return [
                    'offset' => self::CHUNK_SIZE + $offset,
                    'chunk' => $chunk + 1,
                    'size' => filesize($filePath)
                ];
            }

            self::deleteUploadAsync($pk);
            throw new Exception(Yii::t('errors','Can\'t upload one of file fragment'));
        }

        self::deleteUploadAsync($pk);
        throw new Exception(Yii::t('errors','Can\'t read one of file fragment for upload'));
    }

    public static function getFileContainer($id)
    {
        self::$dataAction = 'GetFileContainerList';
        if ($result = self::getModel($id)) {
            return $result;
        }

        return null;
    }

    public static function getFileList($status)
    {
        $postData = [
            'func_name' => 'SearchFileContainer',
            'func_param' => [
                'field_name_list' => ["upload_status"],
                'field_out_list' => [],
                'search_mask_list' => [
                    'upload_status' => [$status]
                ]
            ],
            'lib_name' => self::$dataLib
        ];

        $info = '(not set)';
        $model = new static();
        if (($result = $model->processData($postData)) && isset($result['record_list'])) {
            return $result['record_list'];
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $info = $errorMessage;
        }

        throw new Exception(Yii::t('errors','Can\'t get file list: {info}', ['info' => $info]));
    }

    public static function GetJobStatusList()
    {
        self::$dataAction = 'GetJobStatusList';
        $result = self::getData(['async_func_name' => ['Upload_Init_Async']], null,
            ['field_out_list' => ["id", "status", "async_lib_name", "async_func_name"]]);

        if (!empty($result->list)) {
            return $result->list;
        }

        throw new Exception(Yii::t('errors','Can\'t get job status list'));
    }

    public static function SearchJobStatus($funcName)
    {
        $postData = [
            'func_name' => 'SearchJobStatus',
            'func_param' => [
                'field_name_list' => ["async_func_name"],
                'field_out_list' => [],
                'search_mask_list' => [
                    'async_func_name' => [$funcName]
                ]
            ],
            'lib_name' => self::$dataLib
        ];

        $info = '(not set)';
        $model = new static();
        if (($result = $model->processData($postData)) && isset($result['record_list'])) {
            return $result['record_list'];
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $info = $errorMessage;
        }

        throw new Exception(Yii::t('errors','Can\'t get job status list: {info}', ['info' => $info]));
    }

    public static function downloadFileChunk($pk, $offset)
    {
        $postData = [
            'func_name' => 'Download_NextChunk',
            'func_param' => [
                'PK' => (string)$pk,
                'data_offset' => (string)$offset,
                'data_size' => self::CHUNK_SIZE
            ],
            'lib_name' => self::$dataLib
        ];

        $model = new static();
        if (($result = $model->processData($postData)) && !empty($result['record_list']['file_data'])) {
            if ($return = base64_decode($result['record_list']['file_data'], true)) {
                return $return;
            }
        }

        return null;
    }

    public static function initDownloadFile($pk)
    {
        if ($fileContainer = self::getFileContainer($pk)) {
            $accessRight = DocumentGroup::getAccessPermission($fileContainer['document_family'], $fileContainer['document_category']);
            if ($accessRight == DocumentGroup::ACCESS_RIGHT_READ || $accessRight == DocumentGroup::ACCESS_RIGHT_FULL || $accessRight == DocumentGroup::ACCESS_RIGHT_ADMIN) {
                $fileHashBin = base64_decode($fileContainer['original_file_hash']);
                $fileHashHex = bin2hex($fileHashBin);
                $fileInfo = pathinfo($fileContainer['original_file_name']);

                $directory = self::getDirectory('@webroot');
                $fileName = $fileHashHex . '.' . $fileInfo['extension'];
                $filePath = $directory . DIRECTORY_SEPARATOR . $fileName;

                if (file_exists($filePath)) {
                    return [
                        'file' => self::getDirectory('@web') . '/' . $fileName,
                        'original_name' => $fileContainer['original_file_name'],
						'upload_status' => $fileContainer['upload_status']
                    ];
                }

                if (!is_dir($directory)) {
                    FileHelper::createDirectory($directory);
                }

                if (fopen($filePath, 'w')) {
                    return [
                        'name' => $fileName,
                        'size' => $fileContainer['original_file_size'],
                        'hash_hex' => $fileHashHex,
                        'original_name' => $fileContainer['original_file_name'],
						'upload_status' => $fileContainer['upload_status']
                    ];
                }
            }

            throw new Exception(Yii::t('errors','Access denied'));
        }

        throw new Exception(Yii::t('errors','Can\'t start downloading file from server API'));
    }

    public static function putFileChunk($pk, $filePath, $offset = 0)
    {
        if ($chunkResult = self::downloadFileChunk($pk, $offset)) {
            file_put_contents($filePath, $chunkResult, FILE_APPEND | LOCK_EX);
            return [
                'offset' => self::CHUNK_SIZE + $offset
            ];
        }

        unlink($filePath);
        throw new Exception(Yii::t('errors','Can\'t download one of file fragment from API server'));
    }

    public static function finishDownloadFile($fileName, $fileSize, $fileHash)
    {
        $fileHash = hex2bin($fileHash);
        $filePath = self::getDirectory('@webroot') . DIRECTORY_SEPARATOR . $fileName;
        $downloadedFileHash = hash_file('sha256', $filePath, true);

        if (filesize($filePath) == $fileSize && $fileHash == $downloadedFileHash) {
            return [
                'url' => self::getDirectory('@web') . '/' . $fileName
            ];
        }

        unlink($filePath);
        throw new Exception(Yii::t('errors','File is not downloaded from API server'));
    }

    public static function getDocumentList($kps, $status='true')
    {
        //echo '<pre>'; print_r($kps);

        Yii::$app->response->format = Response::FORMAT_JSON;

        $message = Yii::t('errors', 'Unable to get document list.');

        $field_name_list = array('Active');
        $kp_values = array();

        $search_mask_list['Active'] = [$status];

        foreach($kps as $key => $val) {
            if(!in_array($key, $field_name_list))
               $field_name_list[] = $key;

            $search_mask_list[$key] = $val;
        }

        $postData = [
            'func_name' => 'GetFileContainerList',
            'func_param' => [
                "field_name_list" => $field_name_list,
                "field_value_list" => $search_mask_list
            ],
            'lib_name' => self::$dataLib
        ];

        //echo json_encode($postData);

        $model = new static();

        if (($result = $model->processData($postData)) && !empty($result['record_list'])) {
            foreach ($result['record_list'] as $key => $value) {
                $result['record_list'][$key]['access_right'] = DocumentGroup::getAccessPermission($value['document_family'], $value['document_category']);
            }

            return $result['record_list'];
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $message = $errorMessage;
            throw new Exception($message);
        } else {
            return [];
        }
    }

    public static function deleteDocument($pk, $status='false')
    {
        $postData = [
            'func_name' => 'DeleteFileContainer',
            'func_param' => [
                'PK' => (string) $pk,
                "patch_json" => ["Active" => "$status"]
            ],
            'lib_name' => self::$dataLib
        ];

        $info = '(not set)';

        $model = new static();

        if (($result = $model->processData($postData)) && !empty($result['requestresult']) && $result['requestresult'] == 'successfully') {
            return true;
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $info = $errorMessage;
        }

        throw new Exception(Yii::t('errors','Can\'t delete uploaded file from API server: {info}', ['info' => $info]));
    }

    public static function updateDocument($pk, $category, $description)
    {
        $postData = [
            'func_name' => 'UpdateFileContainer',
            'func_param' => [
                'PK' => (string)$pk,
                'patch_json' => [
                    'document_category' => $category,
                    'Description' => $description
                ]
            ],
            'lib_name' => self::$dataLib
        ];

        $info = '(not set)';

        $model = new static();

        if (($result = $model->processData($postData)) && !empty($result['requestresult']) && $result['requestresult'] == 'successfully') {
            return true;
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            $info = $errorMessage;
        }

        throw new Exception(Yii::t('errors','Can\'t upload chunk of file at number {chunk_num}: {info}', ['chunk_num' => $chunkNum, 'info' => $info]));
    }

    public static function documentDownloadFragment($filePK, $offset, $chunk)
    {
        $postData = [
            'func_name' => 'Download_NextChunk',
            'func_param' => [
                'PK' => (string)$filePK,
                'data_offset' => "$offset",
                'data_size' => "$chunk"
            ],
            'lib_name' => self::$dataLib
        ];

        //if($offset !== 0)
            //echo '<pre>'; print_r($postData);

        $info = '(not set)';

        $model = new static();

        if (($result = $model->processData($postData)) && !empty($result['record_list'])) {
            $list = (!empty($result['record_list'])) ? $result['record_list'] : [];

            return ['list' => $list, 'postData' => $postData];

            //return (!empty($result['record_list'])) ? $result['record_list'] : [];
        } else if ($errorMessage = ExtendedInfo::getErrorMessage()) {
            return $info = $errorMessage;
        }
    }

}