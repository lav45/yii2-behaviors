<?php

namespace lav45\behaviors\tests\models;

use yii\db\ActiveRecord;

/**
 * Class TargetARModel
 * @package lav45\behaviors\tests\models
 *
 * @property int $id
 * @property string $username
 */
class TargetARModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'target_model';
    }
}