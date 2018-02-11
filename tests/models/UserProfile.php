<?php

namespace lav45\behaviors\tests\models;

use yii\db\ActiveRecord;
use lav45\behaviors\PushBehavior;

/**
 * Class UserProfile
 * @package lav45\behaviors\tests\models
 *
 * @property int $user_id
 * @property int $birthday
 *
 * @property User $user
 */
class UserProfile extends ActiveRecord
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
                'class' => PushBehavior::class,
                'relation' => 'apiUser',
                'deleteRelation' => [$this, 'deleteRelation'],
                'createRelation' => false,
                'attributes' => [
                    'birthday' => 'birthday'
                ]
            ]
        ];
    }

    /**
     * @param ApiUser $model
     */
    public function deleteRelation(ApiUser $model)
    {
        $model->birthday = null;
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