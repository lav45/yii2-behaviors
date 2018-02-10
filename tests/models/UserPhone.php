<?php

namespace lav45\behaviors\tests\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use lav45\behaviors\PushBehavior;

/**
 * Class UserPhone
 * @package lav45\behaviors\tests\models
 *
 * @property int $id
 * @property int $user_id
 * @property int $type
 * @property int $phone
 *
 * @property ApiUser $apiUser
 * @property User $user
 */
class UserPhone extends ActiveRecord
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
                'attributes' => [
                    'type' => [
                        'field' => 'phones',
                        'value' => 'apiPhones',
                    ],
                ]
            ]
        ];
    }

    /**
     * @param ApiUser $model
     */
    public function deleteRelation(ApiUser $model)
    {
        $model->phones = $this->getApiPhones(true);
        $model->save(false);
    }

    /**
     * @param bool $excludingSelf
     * @return string
     */
    public function getApiPhones($excludingSelf = false)
    {
        $query = self::find()
            ->select(['id', 'phone', 'type'])
            ->where(['user_id' => $this->user_id])
            ->asArray();

        if ($excludingSelf) {
            $query->andWhere(['not', ['id' => $this->id]]);
        }

        $phones = $query->all();

        if ($phones) {
            $phones = ArrayHelper::map($phones, 'id', 'phone', 'type');
        }

        return json_encode($phones);
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