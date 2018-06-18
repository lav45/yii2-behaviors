# PushBehavior

This extension will help you to dernomalization your data structure, work with Relation.

## Using

```php
$user = new User();
$user->login = 'buster';
$user->name = 'Buster';
$user->status = 10;
$user->save(false);

/**
 * After the User has been automatically created and status = 10 (active) the `TargetModel` 
 * will cause method save (by default) and create User on Ldap
 */

$user = User::findOne($id);
$user->delete();
  
/**
  * After removing `User` and if status = 10 (active) the `TargetModel` 
  * will cause method delete (by default) and delete User on Ldap.
  */

```

- [TargetModel](/examples/models/TargetModel.php)

## Configuration

```
/**
 * @inheritdoc
 */
public function behaviors()
{
    [
        'class' => PushModelBehavior::class,
        'targetClass' => TargetModel::class,
        'triggerAfterInsert' => function (TargetModel $model) {
            //Привязка действий к статусу Модели
            if ($this->status === static::STATUS_ACTIVE) {
                $model->save();
            }
        },
        'triggerAfterUpdate' => function (TargetModel $model) {
            if ($this->status === static::STATUS_ACTIVE) {
                $model->save();
            } else {
                $model->delete();
            }
        },
        'triggerBeforeDelete' => function (TargetModel $model) {
            if ($this->status === static::STATUS_ACTIVE) {
                $model->delete();
            }
        },
        'attributes' => [
            [
                'watch' => ['login', 'status'],
                'field' => 'id',
                'value' => 'id',
            ],
            [
                'watch' => ['login', 'status'],
                'field' => 'login',
                'value' => 'login',
            ],
            [
                'watch' => ['login', 'status'],
                'field' => 'dn',
                'value' => 'dn',
            ],
        ],
    ],
}
```
