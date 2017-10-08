<?php

namespace lav45\behaviors\tests\models;

use yii\db\ActiveRecord;

/**
 * Class News
 * @package lav45\behaviors\tests\models
 *
 * @property integer $id
 * @property string $_data
 *
 * @property string $title
 * @property array $meta
 * @property bool $is_active
 * @property integer $publish_date
 */
class News extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'serialize' => [
                'class' => 'lav45\behaviors\SerializeBehavior',
                'targetAttribute' => '_data',
                'attributes' => [
                    'title',
                    'meta',
                    'is_active',
                    'publish_date',
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

    public function isAttributeChanged($name, $identical = true)
    {
        $serialize = $this->getSerializeBehavior();

        if ($serialize->isAttribute($name)) {
            return $serialize->isAttributeChanged($name, $identical);
        } else {
            return parent::isAttributeChanged($name, $identical);
        }
    }

    public function getOldAttribute($name)
    {
        $serialize = $this->getSerializeBehavior();

        if ($serialize->isAttribute($name)) {
            return $serialize->getOldAttribute($name);
        } else {
            return parent::getOldAttribute($name);
        }
    }
}