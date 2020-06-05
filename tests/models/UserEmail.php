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
            'push' => [
                'class' => PushBehavior::class,
                'relation' => 'apiUser',
                'enable' => static function () {
                    return false;
                },
                'deleteRelation' => [$this, 'deleteRelation'],
                'createRelation' => false,
                'attributes' => [
                    'email' => 'email'
                ]
            ]
        ];
    }

    /**
     * @param ApiUser $model
     */
    public function deleteRelation(ApiUser $model)
    {
        $model->email = null;
        $model->save(false);
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