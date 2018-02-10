# PushBehavior

This extension will help you to denormalization your data structure.


## Using

```php
$user = new User();
$user->login = 'buster';
$user->first_name = 'Buster';
$user->last_name = 'Destroyer';
$user->save(false);

/**
 * After the User has been automatically created the `ApiUser` some attributes from the `User` will be copied to it
 */

$apiUser = ApiUser::findOne($user->id);
print_r($apiUser->attributes);
/**
 * Array
 * (
 *  => [id] => 1
 *  => [user_login] => buster
 *  => [fio] => Buster Destroyer
 *  => [createdAt] => 1517700256
 *  => [updatedAt] => 1517700256
 *     [company_id] => null
 *     [company_name] => null
 *     [phones] => null
 * )
 */


$userPhone = new UserPhone();
$userPhone->user_id = $user->id;
$userPhone->type = 'mobile';
$userPhone->phone = '+123 8561';
$userPhone->save(false);

/**
 * After creating the associated `UserPhone` model, it will find the necessary `ApiUser` model and add self data to it.
 */

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
 *     [company_id] => null
 *     [company_name] => null
 *  => [phones] => {"mobile":{"1":"+123 8561"}}
 * )
 */


$userPhone = new UserPhone();
$userPhone->type = 'work';
$userPhone->phone = '+209 3456';

$user->link('phones', $userPhone);

/**
 * After creating next `UserPhone` model, it will find the necessary `ApiUser` model and add self data to it.
 */

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
 *     [company_id] => null
 *     [company_name] => null
 *  => [phones] => {
 *          "mobile":{"1":"+123 8561"},
 *          "work":{"2":"+209 3456"}
 *     }
 * )
 */


$userPhone->delete();
 
/**
 * After removing `UserPhone`, it will find the necessary `ApiUser` model and remove self data from it.
 */

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
 *     [company_id] => null
 *     [company_name] => null
 *  => [phones] => {"mobile":{"1":"+123 8561"}}
 * )
 */


$company = new Company();
$company->name = 'Ducati';

$user->link('company', $company);

/**
 * After creating the company and adding users model the company will find all related ApiUser model and add their data to them
 */

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
 *  => [company_id] => 1
 *  => [company_name] => Ducati
 *     [phones] => {"work":{"2":"+209 3456"}}
 * )
 */
```


## Configuration

- [User](/tests/models/User.php)
- [UserPhone](/tests/models/UserPhone.php)
- [Company](/tests/models/Company.php)
- [ApiUser](/tests/models/ApiUser.php)
