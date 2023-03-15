<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models\services;

use app\components\_FieldsHelper;

/**
 * Class RecordData
 * @package app\models\services
 *
 * @property RecordManager $recordManager
 */
class RecordData implements RecordDataInterface
{
    private $data = [];
    public $recordManager;

    public function __construct(RecordManagerInterface $recordManager, array $data)
    {
        $this->recordManager = clone $recordManager;
        $this->data = $data;
    }

    public function getNotifications()
    {
        $notifications = [];
        foreach($this->data as $item) {
            if (empty($item['notifications']) || empty($item['valueChanged'])) {
                continue;
            }

            foreach($item['notifications'] as $pk) {
                $notifications[] = [
                    'notify_name' => $pk,
                    'notify_params' => isset($item['notificationParams'][$pk]) ? $item['notificationParams'][$pk] : null,
                    'notify_recipient_list' => isset($item['notificationRecipient'][$pk]) ? $item['notificationRecipient'][$pk] : null
                ];
            }
        }

        return $notifications;
    }

    public function getRecordManager()
    {
        return $this->recordManager;
    }

    public function getData()
    {
        $filteredData = [];
        $type = $this->recordManager->getGroupingFuncType();

		//echo '<pre> $this->data :: '; print_r($this->data);

        foreach($this->data as $item) {
            if (empty($item[$type]) || empty($item['name']) || !isset($item['value'])) {
                continue;
            }

            $item = $this->recordManager->prepareItem($item);
            $function = $item[$type];

            if (strpos($item['name'], '[]')) {
                $item['name'] = str_replace('[]', '', $item['name']);

                if (isset($filteredData[$function][$item['name']])) {
                    $item['value'] .= _FieldsHelper::MULTI_SELECT_DELIMITER . $filteredData[$function][$item['name']];
                }
            }

			if (isset($filteredData[$function][$item['name']]) && $filteredData[$function][$item['name']] != $item['value'])
				$filteredData[$function][$item['name']] = $item['value'];
			else if(!isset($filteredData[$function][$item['name']]))
				$filteredData[$function][$item['name']] = $item['value'];
		}

		//echo '<pre> $filteredData :: '; print_r($filteredData); die;

        return $filteredData;
    }
}