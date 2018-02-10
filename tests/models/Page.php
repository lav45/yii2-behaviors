<?php

namespace lav45\behaviors\tests\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use lav45\behaviors\ReplicationBehavior;

/**
 * Class Page
 * @package lav45\behaviors\tests\models
 *
 * @property integer $id
 * @property string $text
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property PageReplication $pageReplication
 */
class Page extends ActiveRecord
{
    public function transactions()
    {
        return [
            ActiveRecord::SCENARIO_DEFAULT => ActiveRecord::OP_ALL,
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
            ],
            [
                'class' => ReplicationBehavior::class,
                'relation' => 'pageReplication',
                'attributes' => [
                    'id' => 'id',
                    'text' => 'description',
                    'created_at' => [
                        'field' => 'createdAt',
                        'value' => function (self $ownerModel) {
                            return $ownerModel->created_at;
                        }
                    ],
                    'updated_at' => [
                        'field' => 'updatedAt',
                        'value' => 'data.updated'
                    ]
                ]
            ]
        ];
    }

    public function getPageReplication()
    {
        return $this->hasOne(PageReplication::class, ['id' => 'id']);
    }

    public function getData()
    {
        return [
            'id' => $this->id,
            'updated' => $this->updated_at,
            'created' => $this->created_at,
        ];
    }
}