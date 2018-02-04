<?php

namespace lav45\behaviors\tests\models;

use yii\db\ActiveRecord;

/**
 * Class User
 * @package lav45\behaviors\tests\models
 *
 * @property int $id
 * @property string $login
 * @property string $first_name
 * @property string $last_name
 * @property int $created_at
 * @property int $updated_at
 * @property int $last_login
 *
 * @property ApiUser $apiUser
 */
class User extends ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                'class' => 'yii\behaviors\TimestampBehavior',
            ],
            [
                'class' => 'lav45\behaviors\PushBehavior',
                'relation' => 'apiUser',
                'attributes' => [
                    'id' => 'id',

                    'login' => 'user_login',

                    'updated_at' => [
                        'field' => 'updatedAt',
                        'value' => 'data.updated',
                    ],

                    'created_at' => [
                        'field' => 'createdAt',
                        'value' => ['data', 'updated'],
                    ],

                    'last_login' => [
                        'field' => 'lastLogin',
                        'value' => function (self $owner) {
                            return $owner->last_login;
                        }
                    ],

                    'first_name' => [
                        'field' => 'fio',
                        'value' => 'fio', // $this->getFio()
                    ],
                    'last_name' => [
                        'field' => 'fio',
                        'value' => function() {
                            return $this->getFio();
                        },
                    ],
                ]
            ]
        ];
    }

    public function transactions()
    {
        return [
            ActiveRecord::SCENARIO_DEFAULT => ActiveRecord::OP_ALL,
        ];
    }

    public function getFio()
    {
        if (empty($this->last_name)) {
            return $this->first_name;
        }
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getData()
    {
        return [
            'login' => $this->login,
            'updated' => $this->updated_at,
            'created' => $this->created_at,
            'last_login' => $this->last_login,
        ];
    }

    public function getApiUser()
    {
        return $this->hasOne(ApiUser::class, ['id' => 'id']);
    }
}