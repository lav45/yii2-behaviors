<?php

namespace lav45\behaviors\tests\models;

use yii\db\ActiveRecord;
use lav45\behaviors\PushBehavior;

/**
 * Class Company
 * @package lav45\behaviors\tests\models
 *
 * @property int $id
 * @property string $name
 *
 * @property ApiUser[] $apiUsers
 * @property User[] $users
 */
class Company extends ActiveRecord
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
                'relation' => 'apiUsers',
                'deleteRelation' => [$this, 'deleteRelation'],
                'enable' => function() {
                    return $this->getIsNewRecord() === false;
                },
                'attributes' => [
                    'name' => 'company_name',
                ]
            ]
        ];
    }

    /**
     * @param ApiUser $model
     */
    public function deleteRelation(ApiUser $model)
    {
        $model->company_id = null;
        $model->company_name = null;
        $model->save(false);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getApiUsers()
    {
        return $this->hasMany(ApiUser::class, ['id' => 'id'])
            ->via('users');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['company_id' => 'id']);
    }
}