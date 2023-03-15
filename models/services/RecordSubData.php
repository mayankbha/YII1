<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models\services;

use app\components\_FieldsHelper;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * Class RecordSubData
 * @package app\models\services
 *
 * @property RecordManagerInterface $recordManager
 */
class RecordSubData implements RecordDataInterface
{
    private $data = [];
    private $mainData = [];

    public $recordManager;

    public function __construct(RecordManagerInterface $recordManager, array $data)
    {
        $this->recordManager = clone $recordManager;
        $this->data = $data;
    }

    public function setMainData(array $data)
    {
        $this->mainData = $data;
    }

    public function getData()
    {
        $grouping = $this->recordManager->getGroupingFuncType();
        $subDataFiltered = [];

        if ($this->recordManager->isUseAliasFramework()) {
            return $subDataFiltered;
        }

        foreach($this->data as $subPK => $row) {
            $rowFilteredData = [];
            foreach($row as $column) {
                if (empty($column[$grouping]) || empty($column['name']) || !isset($column['value'])) {
                    continue;
                }

                $function = $column[$grouping];
                $column = $this->recordManager->prepareItem($column);

                if (strpos($column['name'], '[]')) {
                    $column['name'] = str_replace('[]', '', $column['name']);
                    if (isset($rowFilteredData[$column['name']])) {
                        $column['value'] .= _FieldsHelper::MULTI_SELECT_DELIMITER . $rowFilteredData[$column['name']];
                    }
                }

                $rowFilteredData[$column['name']] = $column['value'];
            }

            if (!empty($function) && !empty($rowFilteredData)) {
                $subDataFiltered[$function][$subPK] = $rowFilteredData;
            }
        }

        return $subDataFiltered;
    }

    public function getDeleteData()
    {
        $filteredData = [];
        $grouping = $this->recordManager->getGroupingFuncType();

        if ($this->recordManager->isUseAliasFramework()) {
            return $filteredData;
        }

        foreach($this->data as $pk => $config) {
            if (empty($config[0][$grouping])) {
                continue;
            }
            $filteredData[$config[0][$grouping]][] = $pk;
        }
        return $filteredData;
    }

    public function getInsertDataAF()
    {
        $filteredData = [];
        $grouping = $this->recordManager->getGroupingFuncType();

        if (!$this->recordManager->isUseAliasFramework()) {
            return $filteredData;
        }

        $closure = function ($data) {
            $data = $this->recordManager->prepareItem($data);
            return [$data['value']];
        };

        foreach($this->data as $row) {
            $rowData = ArrayHelper::map($row, 'name', $closure, $grouping);
            $filteredData = array_merge_recursive($filteredData, $rowData);
        }

        return $filteredData;
    }

    public function getUpdateDataAF($grouping)
    {
        $filteredData = [];
        $flag = false;

        switch($this->recordManager->getGroupingFuncType()) {
            case RecordManager::ITEM_ATTR_UPDATE_FUNC:
                $flag = 'update';
                break;
            case RecordManager::ITEM_ATTR_CREATE_FUNC:
                $flag = 'insert';
                break;
            case RecordManager::ITEM_ATTR_DELETE_FUNC:
                $flag = 'delete';
                break;
        }

        if (!$this->recordManager->isUseAliasFramework() || !$flag) {
            return $filteredData;
        }

        foreach($this->data as $row) {
            $rowData = [];
            foreach($row as $item) {
                $item = $this->recordManager->prepareItem($item);
                $groupingFunction = $item[$grouping];
                $pkConfig = $item[RecordManager::ITEM_ATTR_AF_PK];

                if (strpos($item['name'], '[]')) {
                    $item['name'] = str_replace('[]', '', $item['name']);
                    if (isset($rowData[$item['name']])) {
                        $item['value'] .= _FieldsHelper::MULTI_SELECT_DELIMITER . $rowData[$item['name']];
                    }
                }

                $rowData[$item['name']] = $item['value'];
            }

            if (!empty($item) && !empty($rowData) && !empty($groupingFunction) && !empty($pkConfig)) {
                $rowData[$flag] = true;
                $rowData['PK'] = $this->getPKConfig($groupingFunction, $pkConfig);

                $filteredData[$groupingFunction][] = $rowData;
            }
        }

        return $filteredData;

    }

    public function getExecuteDataAF($grouping)
    {
        $filteredData = [];
        $flag = false;

        switch($this->recordManager->getGroupingFuncType()) {
            case RecordManager::ITEM_ATTR_UPDATE_FUNC:
                $flag = 'update';
                break;
            case RecordManager::ITEM_ATTR_CREATE_FUNC:
                $flag = 'insert';
                break;
            case RecordManager::ITEM_ATTR_DELETE_FUNC:
                $flag = 'delete';
                break;
        }

        if (!$this->recordManager->isUseAliasFramework() || !$flag) {
            return $filteredData;
        }

        foreach($this->data as $row) {
            $rowData = [];
            foreach($row as $item) {
                $item = $this->recordManager->prepareItem($item);
                $groupingFunction = $item[$grouping];
                $pkConfig = $item[RecordManager::ITEM_ATTR_AF_PK];

                if (strpos($item['name'], '[]')) {
                    $item['name'] = str_replace('[]', '', $item['name']);
                    if (isset($rowData[$item['name']])) {
                        $item['value'] .= _FieldsHelper::MULTI_SELECT_DELIMITER . $rowData[$item['name']];
                    }
                }

                $rowData[$item['name']] = $item['value'];
            }

            if (!empty($item) && !empty($rowData) && !empty($groupingFunction) && !empty($pkConfig)) {
                //$rowData[$flag] = true;
                $rowData['PK'] = $this->getPKConfig($groupingFunction, $pkConfig);

                $filteredData[$groupingFunction][] = $rowData;
            }
        }

        return $filteredData;

    }

    private function getPKConfig($groupingFunction, $config)
    {
        $pk = [];
        if (isset($this->mainData[$groupingFunction]) && !empty($config['key_part'])) {
            if (filter_var($config['use_tenant'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) {
                $pk[] = Yii::$app->getUser()->getIdentity()->tenant_code;
            }
            foreach($config['key_part'] as $key) {
                if (isset($this->mainData[$groupingFunction][$key])) {
                    $pk[] = $this->mainData[$groupingFunction][$key];
                }
            }
        }

        return implode(RecordManager::PK_DELIMITER, $pk);
    }
}