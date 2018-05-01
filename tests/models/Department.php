<?php

namespace lav45\behaviors\tests\models;

use yii\db\ActiveRecord;
use yii\behaviors\AttributeTypecastBehavior;

/**
 * Class Department
 * @package lav45\behaviors\tests\models
 *
 * @property int $id
 * @property int $parent_id
 * @property string $name
 * @property int $user_count
 * @property int $user_count_in_subdivision
 * @property int $user_total_count
 *
 * @property User[] $users
 * @property Department $parent
 */
class Department extends ActiveRecord
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
    public function rules()
    {
        return [
            [[
                'user_count',
                'user_count_in_subdivision',
                'user_total_count',
            ], 'default', 'value' => 0],
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => AttributeTypecastBehavior::class
            ],
        ];
    }

    public function beforeSave($insert)
    {
        $this->user_total_count = $this->user_count + $this->user_count_in_subdivision;

        if ($this->parent_id !== null) {
            $count = $this->user_total_count;
            if ($this->isAttributeChanged('parent_id') === false) {
                $count -= $this->getOldAttribute('user_total_count');
            }
            if ($count !== 0) {
                $this->parent->user_count_in_subdivision += $count;
                $this->parent->save(false);
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['department_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(static::class, ['id' => 'parent_id']);
    }
}