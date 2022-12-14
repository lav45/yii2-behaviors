<?php

namespace lav45\behaviors\tests\models;

use lav45\behaviors\PushBehavior;
use yii\db\ActiveRecord;

/**
 * Class UserEmail
 * @package lav45\behaviors\tests\models
 *
 * @property int $user_id
 * @property string $email
 *
 * @property User $user
 */
class UserEmail extends ActiveRecord
{
    /** @var bool */
    public $enable = true;

    /**
     * @return array
     */
    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'push' => [
                'class' => PushBehavior::class,
                'relation' => 'apiUser',
                'enable' => function () {
                    return $this->enable;
                },
                'createRelation' => false,
                'updateRelationAfterInsert' => true,
                'attributes' => [
                    'email' => 'email'
                ]
            ]
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApiUser()
    {
        return $this->hasOne(ApiUser::class, ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function User()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}