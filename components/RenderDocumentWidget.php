<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\components;

use app\models\CommandData;
use Yii;
use app\models\DocumentGroup;
use app\models\FileModel;
use yii\helpers\Html;

class RenderDocumentWidget extends BaseRenderWidget
{
    public $pk;
    public $dataField;
    public function run()
    {
        $this->dataField = CommandData::fixedApiResult($this->configuration->layout_configuration->params[0], $this->_alias_framework->enable);
        $this->pk = (!empty($this->data[$this->dataField])) ? $this->data[$this->dataField] : null;

        if ($fileContainer = FileModel::getFileContainer($this->pk)) {
            $accessRight = DocumentGroup::getAccessPermission($fileContainer['document_family'],  $fileContainer['document_category']);

			if ($accessRight == DocumentGroup::ACCESS_RIGHT_READ || $accessRight == DocumentGroup::ACCESS_RIGHT_FULL || $accessRight == DocumentGroup::ACCESS_RIGHT_ADMIN) {
                $fileHashBin = base64_decode($fileContainer['original_file_hash']);
                $fileInfo = pathinfo($fileContainer['original_file_name']);

                $fileName = bin2hex($fileHashBin) . '.' . $fileInfo['extension'];
                $frameClass = str_replace(".", "", microtime(true));
                $src = Yii::getAlias('@web') . '/file/show?name=' . $fileName;
                $downloadField = null;

                if (!file_exists(FileModel::getDirectory('@webroot', '/') . DIRECTORY_SEPARATOR . $fileName)) {
                    $downloadField = $this->getDownloadField($frameClass);
                }

                return $downloadField . Html::tag('iframe', null, [
                    'class' => "document-iframe $frameClass",
                    'src' => ($downloadField) ? null : $src,
                    'data-src' => ($downloadField) ? $src : null,
                    'width' => '100%',
                    'height' => '340',
                    'allowfullscreen' => true,
                    'webkitallowfullscreen' => true,
                    'style' => ($downloadField) ? 'display: none' : null
                ]);
            }

            return $this->render('/file/error_frame', ['message' => 'Access denied']);
        }

        return $this->render('/file/error_frame', ['message' => 'Can\'t find file container']);
    }

    public function getDownloadField($frameClass)
    {
        $widgetConfig = [
            'libName' => $this->lib_name,
            'value' => $this->pk,
            'dataField' => $this->dataField,
            'dataAccess' => $this->dataAccess,
            'config' => [
                'field_type' => _FieldsHelper::TYPE_DOCUMENT,
                'related_frame_class' => $frameClass,
                'is_viewer' => true
            ],
            'data_source_get' => $this->configuration->data_source_get
        ];

        if ($this->_alias_framework->enable) {
            $widgetConfig['data_source_update'] = $this->_alias_framework->data_source_update;
            $widgetConfig['data_source_delete'] = $this->_alias_framework->data_source_delete;
            $widgetConfig['data_source_create'] = $this->_alias_framework->data_source_insert;
        }

        return _FieldsHelper::widget($widgetConfig);
    }
}