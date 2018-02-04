<?php

namespace lav45\behaviors\tests\tests;

use lav45\behaviors\tests\models\User;
use lav45\behaviors\tests\models\ApiUser;
use lav45\behaviors\tests\models\UserPhone;
use lav45\behaviors\tests\models\UserProfile;

class PushBehaviorTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $user = new User();
        $user->login = 'buster';
        $user->first_name = 'Buster';

        $this->assertTrue($user->save(false));
        $this->assertTrue($user->apiUser instanceof ApiUser);
        $this->assertFalse($user->apiUser->getIsNewRecord());

        /** @var ApiUser $apiUser */
        $apiUser = ApiUser::findOne($user->id);
        $this->assertNotNull($apiUser);
        $this->assertEquals($apiUser->id, $user->id);
        $this->assertEquals($apiUser->user_login, $user->login);
        $this->assertEquals($apiUser->fio, $user->getFio());
        $this->assertEquals($apiUser->updatedAt, $user->updated_at);
        $this->assertEquals($apiUser->createdAt, $user->created_at);
        $this->assertEquals($apiUser->lastLogin, $user->last_login);


        $userProfile = new UserProfile();
        $userProfile->user_id = $user->id;
        $userProfile->birthday = time();
        $this->assertTrue($userProfile->save(false));

        /** @var ApiUser $apiUser */
        $apiUser = ApiUser::findOne($user->id);
        $this->assertEquals($apiUser->birthday, $userProfile->birthday);


        $phones = [];
        $userPhone = new UserPhone();
        $userPhone->user_id = $user->id;
        $userPhone->type = 'house';
        $userPhone->phone = '+122 3456';
        $this->assertTrue($userPhone->save(false));
        $phones[$userPhone->type][$userPhone->id] = $userPhone->phone;

        /** @var ApiUser $apiUser */
        $apiUser = ApiUser::findOne($user->id);
        $this->assertEquals($apiUser->phones, $userPhone->getApiPhones());

        $userPhone = new UserPhone();
        $userPhone->user_id = $user->id;
        $userPhone->type = 'work';
        $userPhone->phone = '+209 3456';
        $this->assertTrue($userPhone->save(false));
        $phones[$userPhone->type][$userPhone->id] = $userPhone->phone;

        $userPhone = new UserPhone();
        $userPhone->user_id = $user->id;
        $userPhone->type = 'work';
        $userPhone->phone = '+209 3555';
        $this->assertTrue($userPhone->save(false));
        $phones[$userPhone->type][$userPhone->id] = $userPhone->phone;

        /** @var ApiUser $apiUser */
        $apiUser = ApiUser::findOne($user->id);
        $this->assertEquals($apiUser->phones, $userPhone->getApiPhones());
        $this->assertEquals(json_decode($apiUser->phones, true), $phones);
    }

    public function testUpdate()
    {
        /** @var User $user */
        $user = User::find()->one();
        $this->assertNotNull($user);

        $user->last_name = 'Destroyer';
        $user->last_login = time();

        $this->assertTrue($user->save(false));

        /** @var ApiUser $apiUser */
        $apiUser = ApiUser::findOne($user->id);
        $this->assertNotNull($apiUser);
        $this->assertEquals($apiUser->id, $user->id);
        $this->assertEquals($apiUser->user_login, $user->login);
        $this->assertEquals($apiUser->fio, $user->getFio());
        $this->assertEquals($apiUser->updatedAt, $user->updated_at);
        $this->assertEquals($apiUser->createdAt, $user->created_at);
        $this->assertEquals($apiUser->lastLogin, $user->last_login);


        /** @var UserProfile $userProfile */
        $userProfile = UserProfile::findOne(['user_id' => $user->id]);
        $userProfile->birthday = time() - 100;
        $this->assertTrue($userProfile->save(false));

        /** @var ApiUser $apiUser */
        $apiUser = ApiUser::findOne($user->id);
        $this->assertEquals($apiUser->birthday, $userProfile->birthday);


        /** @var UserPhone $userPhone */
        $userPhone = UserPhone::findOne(['user_id' => $user->id]);
        $userPhone->type = 'mobile';
        $userPhone->phone = '+123 8561';

        $phones = json_decode($apiUser->phones, true);
        $phones[$userPhone->type][$userPhone->id] = $userPhone->phone;
        $type = $userPhone->getOldAttribute('type');
        unset($phones[$type][$userPhone->id]);
        if (empty($phones[$type])) {
            unset($phones[$type]);
        }

        $this->assertTrue($userPhone->save(false));

        /** @var ApiUser $apiUser */
        $apiUser = ApiUser::findOne($user->id);
        $this->assertEquals($apiUser->phones, $userPhone->getApiPhones());
        $this->assertEquals(json_decode($apiUser->phones, true), $phones);
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function testDelete()
    {
        /** @var User $user */
        $user = User::find()->one();

        /** @var ApiUser $apiUser */
        $apiUser = ApiUser::findOne($user->id);

        /** @var UserPhone $userPhone */
        $userPhone = UserPhone::findOne(['user_id' => $user->id]);

        $phones = json_decode($apiUser->phones, true);
        unset($phones[$userPhone->type][$userPhone->id]);
        if (empty($phones[$userPhone->type])) {
            unset($phones[$userPhone->type]);
        }

        $this->assertEquals($userPhone->delete(), 1);

        /** @var ApiUser $apiUser */
        $apiUser = ApiUser::findOne($user->id);
        $this->assertNotNull($apiUser);

        $expectedPhones = (new UserPhone(['user_id' => $user->id]))->getApiPhones();
        $this->assertEquals($expectedPhones, $apiUser->phones);
        $this->assertEquals($phones, json_decode($apiUser->phones, true));

        $userProfile = UserProfile::findOne(['user_id' => $user->id]);
        $this->assertEquals($userProfile->delete(), 1);

        /** @var ApiUser $apiUser */
        $apiUser = ApiUser::findOne($user->id);
        $this->assertNull($apiUser->birthday);

        $this->assertEquals($user->delete(), 1);
        $this->assertNull(ApiUser::findOne($user->id));
    }
}