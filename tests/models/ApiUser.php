<?php

namespace lav45\behaviors\tests\models;

use yii\db\ActiveRecord;

/**
 * Class ApiUser
 * @package lav45\behaviors\tests\models
 *
 * @property int $id
 * @property string $user_login
 * @property string $fio
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $lastLogin
 * @property int $birthday
 * @property string $phones
 * @property int $company_id
 * @property string $company_name
 */
class ApiUser extends ActiveRecord
{
}