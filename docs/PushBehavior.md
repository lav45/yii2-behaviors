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

- [User](/tests/models/User.php)
- [UserPhone](/tests/models/UserPhone.php)
- [ApiUser](/tests/models/ApiUser.php)
