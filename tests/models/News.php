<?php

namespace lav45\behaviors\tests\models;

use yii\db\ActiveRecord;
use lav45\behaviors\VirtualAttributesTrait;

/**
 * Class News
 * @package lav45\behaviors\tests\models
 *
 * @property integer $id
 * @property string $title
 * @property string $_data
 * @property string $_tags
 * @property string $_options
 *
 * Virtual attributes
 * ---------------------------
 * @property string $description
 * @property array $meta
 * @property bool $is_active
 * @property int $defaultValue
 * @property int $defaultFunc
 *
 * @property array $tags
 * @property array $options
 */
class News extends ActiveRecord
{
    use VirtualAttributesTrait;

    public function behaviors()
    {
        return [
            'serialize' => [
                'class' => 'lav45\behaviors\SerializeBehavior',
                'storageAttribute' => '_data',
                'attributes' => [
                    'description',
                    'meta' => [
                        'keywords' => null,
                        'description' => null,
                    ],
                    'is_active' => true,
                    'defaultValue' => 1,
                    'defaultFunc' => function() {
                        return $this->id;
                    }
                ]
            ],
            'serializeProxy' => [
                'class' => 'lav45\behaviors\SerializeProxyBehavior',
                'attributes' => [
                    'tags' => '_tags',
                    'options' => '_options',
                ]
            ]
        ];
    }
}