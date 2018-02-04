<?php

namespace lav45\behaviors\tests\models;

use yii\db\ActiveRecord;

/**
 * Class UserProfile
 * @package lav45\behaviors\tests\models
 *
 * @property int $user_id
 * @property int $birthday
 */
class UserProfile extends ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                'class' => 'lav45\behaviors\PushBehavior',
                'relation' => 'apiUser',
                'deleteRelation' => [$this, 'deleteRelation'],
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

    public function transactions()
    {
        return [
            ActiveRecord::SCENARIO_DEFAULT => ActiveRecord::OP_ALL,
        ];
    }

    public function getApiUser()
    {
        return $this->hasOne(ApiUser::class, ['id' => 'user_id']);
    }
}