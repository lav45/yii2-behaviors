<?php

namespace lav45\behaviors\tests\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

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
 */
class UserPhone extends ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                'class' => 'lav45\behaviors\PushBehavior',
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