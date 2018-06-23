<?php

namespace lav45\behaviors\tests\tests;

use Yii;
use lav45\behaviors\tests\models\User;
use lav45\behaviors\tests\models\ApiUser;
use lav45\behaviors\tests\models\UserPhone;
use lav45\behaviors\tests\models\UserProfile;
use lav45\behaviors\tests\models\Company;
use PHPUnit\Framework\TestCase;

class PushBehaviorTest extends TestCase
{
    /**
     * To ensure that during the test, the base does not increase in size
     * @param string|array $tables
     * @throws \yii\db\Exception
     */
    public function clearTable($tables)
    {
        $command = Yii::$app->getDb()->createCommand();
        foreach ((array)$tables as $table) {
            $command->truncateTable($table)->execute();
        }
    }

    public function testCRUDTargetModel()
    {
        // Create
        $user = new User();
        $user->login = 'buster';
        $user->first_name = 'Buster';

        $this->assertTrue($user->save(false));
        $this->assertInstanceOf(ApiUser::class, $user->apiUser);
        $this->assertFalse($user->apiUser->getIsNewRecord());

        $apiUser = $this->getApiUser($user->id);
        $this->assertNotNull($apiUser);
        $this->assertEquals($user->id, $apiUser->id);
        $this->assertEquals($user->login, $apiUser->user_login);
        $this->assertEquals($user->getFio(), $apiUser->fio);
        $this->assertEquals($user->updated_at, $apiUser->updatedAt);
        $this->assertEquals($user->created_at, $apiUser->createdAt);
        $this->assertEquals($user->last_login, $apiUser->lastLogin);

        // Update
        $user->last_name = 'Destroyer';
        $user->last_login = time();
        $this->assertTrue($user->save(false));

        $apiUser->refresh();
        $this->assertEquals($user->getFio(), $apiUser->fio);
        $this->assertEquals($user->last_login, $apiUser->lastLogin);

        // Delete
        $this->assertEquals($user->delete(), 1);
        $this->assertNull($this->getApiUser($user->id));

        $this->clearTable([
            User::tableName(),
            ApiUser::tableName(),
        ]);
    }

    public function testCRUDOneRelationModel()
    {
        $user = $this->createUser();

        // Create
        $userProfile = new UserProfile();
        $userProfile->birthday = time();
        $user->link('profile', $userProfile);

        $apiUser = $this->getApiUser($user->id);
        $this->assertEquals($userProfile->birthday, $apiUser->birthday);

        // Update
        $userProfile->birthday = time() - 100;
        $this->assertTrue($userProfile->save(false));

        $apiUser->refresh();
        $this->assertEquals($userProfile->birthday, $apiUser->birthday);

        //Update Closure
        $flag = false;
        /** @var \lav45\behaviors\PushBehavior $behavior */
        $behavior = $userProfile->getBehavior('push');
        $behavior->updateRelation = function (ApiUser $model) use (&$flag) {
            $flag = true;
            $model->save(false);
        };

        $userProfile->birthday = time() - 200;
        $this->assertTrue($userProfile->save(false));

        $apiUser->refresh();
        $this->assertEquals($userProfile->birthday, $apiUser->birthday);
        $this->assertTrue($flag);

        // Delete
        $this->assertEquals($userProfile->delete(), 1);

        $apiUser->refresh();
        $this->assertNull($apiUser->birthday);

        $this->clearTable([
            User::tableName(),
            ApiUser::tableName(),
            UserProfile::tableName(),
        ]);
    }

    public function testCRUDManyRelationModel()
    {
        $user = $this->createUser();

        // Create
        $phones = [];
        $userPhone_1 = new UserPhone();
        $userPhone_1->user_id = $user->id;
        $userPhone_1->type = 'house';
        $userPhone_1->phone = '+122 3456';
        $this->assertTrue($userPhone_1->save(false));
        $phones[$userPhone_1->type][$userPhone_1->id] = $userPhone_1->phone;

        $apiUser = $this->getApiUser($user->id);
        $this->assertEquals($phones, json_decode($apiUser->phones, true));

        $userPhone_2 = new UserPhone();
        $userPhone_2->user_id = $user->id;
        $userPhone_2->type = 'work';
        $userPhone_2->phone = '+209 3456';
        $this->assertTrue($userPhone_2->save(false));
        $phones[$userPhone_2->type][$userPhone_2->id] = $userPhone_2->phone;

        $apiUser->refresh();
        $this->assertEquals($phones, json_decode($apiUser->phones, true));

        $userPhone_3 = new UserPhone();
        $userPhone_3->user_id = $user->id;
        $userPhone_3->type = 'work';
        $userPhone_3->phone = '+209 3555';
        $this->assertTrue($userPhone_3->save(false));
        $phones[$userPhone_3->type][$userPhone_3->id] = $userPhone_3->phone;

        $apiUser->refresh();
        $this->assertEquals($phones, json_decode($apiUser->phones, true));

        // Update
        unset($phones[$userPhone_1->type]);

        $userPhone_1->type = 'mobile';
        $userPhone_1->phone = '+123 8561';
        $phones[$userPhone_1->type][$userPhone_1->id] = $userPhone_1->phone;

        $this->assertTrue($userPhone_1->save(false));

        $apiUser->refresh();
        $this->assertEquals($phones, json_decode($apiUser->phones, true));

        // Delete
        unset($phones[$userPhone_1->type]);
        $this->assertEquals($userPhone_1->delete(), 1);

        $this->assertTrue($apiUser->refresh());
        $this->assertEquals($phones, json_decode($apiUser->phones, true));

        $this->clearTable([
            User::tableName(),
            ApiUser::tableName(),
            UserPhone::tableName(),
        ]);
    }

    public function testCRUDManyTargetRelationModel()
    {
        // ========== Create ==========
        // Create Users
        $user_1 = new User();
        $user_1->login = 'buster';
        $user_1->first_name = 'Buster';
        $user_1->save(false);

        $user_2 = new User();
        $user_2->login = 'lusya';
        $user_2->first_name = 'Lusya';
        $user_2->save(false);

        // Create company
        $company_1 = new Company();
        $company_1->name = 'Harley';
        $company_1->save(false);

        // Add users in to the company
        $user_1->link('company', $company_1);
        $user_2->link('company', $company_1);

        // Check save data
        $this->assertEquals($company_1->name, $this->getApiUser($user_1->id)->company_name);
        $this->assertEquals($company_1->name, $this->getApiUser($user_2->id)->company_name);

        // ========== Update ==========
        // Update relation model
        $company_1->name = 'Harley-Davidson';
        $company_1->save(false);

        // Check save data
        $this->assertEquals($company_1->name, $this->getApiUser($user_1->id)->company_name);
        $this->assertEquals($company_1->name, $this->getApiUser($user_2->id)->company_name);

        // Retarget
        $company_2 = new Company();
        $company_2->name = 'Ducati';
        $this->assertTrue($company_2->save(false));

        // Move users in to the new company
        $user_1->link('company', $company_2);
        $user_2->link('company', $company_2);

        // Check save data
        $apiUser_1 = $this->getApiUser($user_1->id);
        $this->assertEquals($company_2->id, $apiUser_1->company_id);
        $this->assertEquals($company_2->name, $apiUser_1->company_name);

        $apiUser_2 = $this->getApiUser($user_2->id);
        $this->assertEquals($company_2->id, $apiUser_2->company_id);
        $this->assertEquals($company_2->name, $apiUser_2->company_name);

        // ========== Delete ==========
        // User leave the company
        $user_1->unlink('company', $company_2);

        $apiUser_1->refresh();
        $this->assertNull($apiUser_1->company_id);
        $this->assertNull($apiUser_1->company_name);

        // Remove company
        $this->assertEquals($company_2->delete(), 1);

        $apiUser_2->refresh();
        $this->assertNull($apiUser_2->company_id);
        $this->assertNull($apiUser_2->company_name);

        $this->clearTable([
            User::tableName(),
            ApiUser::tableName(),
            Company::tableName(),
        ]);
    }

    /**
     * @return User
     */
    private function createUser()
    {
        $user = new User();
        $user->login = 'buster';
        $user->first_name = 'Buster';
        $user->save(false);
        return $user;
    }

    /**
     * @param int $id
     * @return null|ApiUser
     */
    private function getApiUser($id)
    {
        return ApiUser::findOne($id);
    }
}