<?php

namespace lav45\behaviors\tests\models;

use yii\db\ActiveRecord;
use lav45\behaviors\PushModelBehavior;

/**
 * Class PushModel
 * @package lav45\behaviors\tests\models
 *
 * @property int $id
 * @property string $username
 */
class PushModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'push_model';
    }

    public function behaviors()
    {
        return [
            'push' => [
                'class' => PushModelBehavior::class,
                'targetClass' => TargetModel::class,
                'attributes' => [
                    'id' => [
                        'watch' => true,
                    ],
                    'username' => 'login',
                ]
            ]
        ];
    }
}