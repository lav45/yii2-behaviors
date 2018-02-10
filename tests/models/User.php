<?php

namespace lav45\behaviors\tests\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use lav45\behaviors\PushBehavior;

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
 * @property int $company_id
 *
 * @property ApiUser $apiUser
 * @property Company $company
 * @property UserProfile $profile
 * @property UserPhone $phones
 */
class User extends ActiveRecord
{
    /**
     * @return array
     */
    public function transactions()
    {
        return [
            ActiveRecord::SCENARIO_DEFAULT => ActiveRecord::OP_ALL,
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
            ],
            [
                'class' => PushBehavior::class,
                'relation' => 'apiUser',
                'attributes' => [
                    'id',

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

                    [
                        'watch' => ['first_name', 'last_name'],
                        'field' => 'fio',
                        'value' => 'fio', // $this->getFio()
                    ],

                    'company_id' => 'company_id',

                    [
                        'watch' => 'company_id', // if changed attribute `company_id`
                        'value' => 'company.name', // then get value from the $this->company->name
                        'field' => 'company_name', // and set this value in to the relation attribute `company_name`
                    ]
                ]
            ]
        ];
    }

    /**
     * @return string
     */
    public function getFio()
    {
        if (empty($this->last_name)) {
            return $this->first_name;
        }
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'login' => $this->login,
            'updated' => $this->updated_at,
            'created' => $this->created_at,
            'last_login' => $this->last_login,
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApiUser()
    {
        return $this->hasOne(ApiUser::class, ['id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'company_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(UserProfile::class, ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhones()
    {
        return $this->hasMany(UserPhone::class, ['user_id' => 'id']);
    }
}