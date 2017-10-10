<?php

namespace lav45\behaviors\tests\models;

use yii\db\ActiveRecord;

/**
 * Class News
 * @package lav45\behaviors\tests\models
 *
 * @property integer $id
 * @property string $_data
 * @property string $_tags
 * @property string $_options
 *
 * Virtual attributes
 * ---------------------------
 * @property string $title
 * @property array $meta
 * @property bool $is_active
 * @property int $defaultValue
 * @property int $defaultFunc
 * @property array $tags
 * @property array $options
 */
class News extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'serialize' => [
                'class' => 'lav45\behaviors\SerializeBehavior',
                'storageAttribute' => '_data',
                'attributes' => [
                    'title',
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

    /**
     * @return \lav45\behaviors\SerializeBehavior
     */
    public function getSerializeBehavior()
    {
        /** @var \lav45\behaviors\SerializeBehavior $behavior */
        $behavior = $this->getBehavior('serialize');
        return $behavior;
    }

    /**
     * @return \lav45\behaviors\SerializeProxyBehavior
     */
    public function getSerializeProxyBehavior()
    {
        /** @var \lav45\behaviors\SerializeProxyBehavior $behavior */
        $behavior = $this->getBehavior('serializeProxy');
        return $behavior;
    }

    /**
     * @param string $name
     * @param bool $identical
     * @return bool
     */
    public function isAttributeChanged($name, $identical = true)
    {
        $serialize = $this->getSerializeBehavior();
        if ($serialize->canGetProperty($name)) {
            return $serialize->isAttributeChanged($name, $identical);
        }

        $serializeProxy = $this->getSerializeProxyBehavior();
        if ($serializeProxy->canGetProperty($name)) {
            return $serializeProxy->isAttributeChanged($name, $identical);
        }

        return parent::isAttributeChanged($name, $identical);
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getOldAttribute($name)
    {
        $serialize = $this->getSerializeBehavior();
        if ($serialize->canGetProperty($name)) {
            return $serialize->getOldAttribute($name);
        }

        $serializeProxy = $this->getSerializeProxyBehavior();
        if ($serializeProxy->canGetProperty($name)) {
            return $serializeProxy->getOldAttribute($name);
        }

        return parent::getOldAttribute($name);
    }
}