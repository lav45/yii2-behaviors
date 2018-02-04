# PushBehavior

This extension will help you to denormalize your data structure.


## Using

```php
$user = new User();
$user->login = 'buster';
$user->first_name = 'Buster';
$user->last_name = 'Destroyer';
$user->save(false);

$userPhone = new UserPhone();
$userPhone->user_id = $user->id;
$userPhone->type = 'mobile';
$userPhone->phone = '+123 8561';
$userPhone->save(false);

$userPhone = new UserPhone();
$userPhone->user_id = $user->id;
$userPhone->type = 'work';
$userPhone->phone = '+209 3456';
$userPhone->save(false);

$apiUser = ApiUser::findOne($user->id);
print_r($apiUser->attributes);
/**
 * Array
 * (
 *     [id] => 1
 *     [user_login] => buster
 *     [fio] => Buster Destroyer
 *     [createdAt] => 1517700256
 *     [updatedAt] => 1517700256
 *     [lastLogin] => null
 *     [birthday] => null
 *     [phones] => {"mobile":{"1":"+123 8561"},"work":{"2":"+209 3456"}}
 * )
 */
 
 $userPhone->delete();
 
 $apiUser = ApiUser::findOne($user->id);
 print_r($apiUser->attributes);
 /**
  * Array
  * (
  *     [id] => 1
  *     [user_login] => buster
  *     [fio] => Buster Destroyer
  *     [createdAt] => 1517700256
  *     [updatedAt] => 1517700256
  *     [lastLogin] => null
  *     [birthday] => null
  *     [phones] => {"mobile":{"1":"+123 8561"}}
  * )
  */
```


## Configuration

```php
/**
 * Class User
 *
 * @property int $id
 * @property string $login
 * @property string $first_name
 * @property string $last_name
 * @property int $created_at
 * @property int $updated_at
 * @property int $last_login
 *
 * @property ApiUser $apiUser
 */
class User extends ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                'class' => 'yii\behaviors\TimestampBehavior',
            ],
            [
                'class' => 'lav45\behaviors\PushBehavior',
                'relation' => 'apiUser',
                'attributes' => [
                    'id' => 'id',
                    'login' => 'user_login',
                    'updated_at' => 'updatedAt',
                    'created_at' => 'createdAt',

                    'last_login' => [
                        'field' => 'lastLogin',
                        'value' => function (self $owner) {
                            return $owner->last_login;
                        }
                    ],

                    'first_name' => [
                        'field' => 'fio',
                        'value' => 'fio', // $this->getFio()
                    ],
                    'last_name' => [
                        'field' => 'fio',
                        'value' => function() {
                            return $this->getFio();
                        },
                    ],
                ]
            ]
        ];
    }

    public function transactions()
    {
        return [
            ActiveRecord::SCENARIO_DEFAULT => ActiveRecord::OP_ALL,
        ];
    }

    public function getFio()
    {
        if (empty($this->last_name)) {
            return $this->first_name;
        }
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getApiUser()
    {
        return $this->hasOne(ApiUser::class, ['id' => 'id']);
    }
}

/**
 * Class UserPhone
 *
 * @property int $id
 * @property int $user_id
 * @property int $type
 * @property int $phone
 *
 * @property ApiUser $apiUser
 * @property UserPhone[] $allPhones
 */
class UserPhone extends ActiveRecord
{
    private $beforeDelete = false;

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

    public function deleteRelation(ApiUser $model)
    {
        $model->phones = $this->getApiPhones(true);
        $model->save(false);
    }

    public function getApiPhones($excludingSelf = false)
    {
        $query = $this->getAllPhones()
            ->select(['id', 'phone', 'type'])
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

    public function getAllPhones()
    {
        return $this->hasMany(self::class, ['user_id' => 'user_id']);
    }
}

/**
 * Class ApiUser
 *
 * @property int $id
 * @property string $user_login
 * @property string $fio
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $lastLogin
 * @property string $phones
 */
class ApiUser extends ActiveRecord
{
}
```