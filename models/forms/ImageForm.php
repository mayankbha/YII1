<?php
/**
 * @copyright 2017 Champion Computer Consulting Inc. - All Rights Reserved.
 */

namespace app\models\forms;

use Yii;
use yii\base\Model;

class ImageForm extends Model
{
    public $pk;
    public $list_name;
    public $entry_name;
    public $type;
    public $description = '';
    public $logo_image_body;

    const TYPE_LOGO_HEADER = 'LOGO_HEADER';
    const TYPE_LOGO_MAIN = 'LOGO_MAIN';
    const TYPE_IMAGE = 'IMAGE';

    public static $types = [
        self::TYPE_LOGO_HEADER => 'Header logo',
        self::TYPE_LOGO_MAIN => 'Main logo',
    ];


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['list_name', 'entry_name', 'type'],'required'],
            ['logo_image_body','file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg'],
            ['description', 'string', 'max' => 255],
            ['pk', 'safe']
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'list_name' => Yii::t('app', 'List name'),
            'entry_name' => Yii::t('app', 'Entry name'),
            'type' => Yii::t('app', 'Type'),
            'logo_image_body' => Yii::t('app', 'Image'),
            'description' => Yii::t('app', 'Description'),
        ];
    }
}