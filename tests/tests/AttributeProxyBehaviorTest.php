<?php

namespace lav45\behaviors\tests\tests;

use lav45\behaviors\AttributeProxyBehavior;
use lav45\behaviors\tests\models\User;
use lav45\behaviors\tests\models\UserProfile;
use PHPUnit\Framework\TestCase;
use Yii;

/**
 * Class AttributeProxyBehaviorTest
 * @package lav45\behaviors\tests\tests
 */
class AttributeProxyBehaviorTest extends TestCase
{
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
     * To ensure that during the test, the base does not increase in size
     * @param array $tables
     * @throws \yii\db\Exception
     */
    protected function clearTable(array $tables)
    {
        $command = Yii::$app->getDb()->createCommand();
        foreach ($tables as $table) {
            $command->truncateTable($table)->execute();
        }
    }

    public function testSaveWithoutChange()
    {
        $user = $this->createUser();
        $user->attachBehavior('attributeProxy', [
            'class' => AttributeProxyBehavior::class,
            'attributes' => [
                'birthday' => 'profile.birthday',
            ],
        ]);

        self::assertTrue($user->save(false));

        $user->refresh();
        self::assertNull($user->profile);
        self::assertNotNull($user->apiUser);

        $this->clearTable(['user', 'api_user']);
    }

    public function testCreateRelation()
    {
        $user = $this->createUser();
        $user->attachBehavior('attributeProxy', [
            'class' => AttributeProxyBehavior::class,
            'attributes' => [
                'birthday' => 'profile.birthday',
            ],
        ]);

        $user->birthday = time();
        self::assertTrue($user->save(false));

        $user->refresh();
        self::assertNotNull($user->profile);
        self::assertNotNull($user->apiUser);

        $this->clearTable(['user', 'user_profile', 'api_user']);
    }

    public function testUpdateRelation()
    {
        $user = $this->createUser();
        $user->attachBehavior('attributeProxy', [
            'class' => AttributeProxyBehavior::class,
            'attributes' => [
                'birthday' => 'profile.birthday',
            ],
        ]);

        $old_time = $user->birthday = time();
        self::assertEquals($old_time, $user->profile->birthday);
        self::assertTrue($user->save(false));

        self::assertNotNull($user->profile);
        $user->profile->refresh();
        self::assertEquals($old_time, $user->profile->birthday);

        $new_tome = $old_time + 100;
        $user->birthday = $new_tome;

        self::assertEquals($new_tome, $user->profile->birthday);
        self::assertTrue($user->save(false));

        $user->profile->refresh();
        self::assertEquals($new_tome, $user->profile->birthday);

        $this->clearTable(['user', 'user_profile', 'api_user']);
    }

    public function testDeleteNewRelation()
    {
        $user = $this->createUser();
        $user->attachBehavior('attributeProxy', [
            'class' => AttributeProxyBehavior::class,
            'attributes' => [
                'birthday' => 'profile.birthday',
            ],
        ]);

        $user->birthday = time();
        self::assertTrue($user->save(false));
        self::assertNotNull($user->profile);

        $user_id = $user->id;
        $user->delete();

        $profile = UserProfile::findOne(['user_id' => $user_id]);
        self::assertNotNull($profile);
        self::assertNull($profile->birthday);

        $this->clearTable(['user', 'user_profile', 'api_user']);
    }

    public function testGetDefaultAttribute()
    {
        $user = $this->createUser();
        $user->attachBehavior('attributeProxy', [
            'class' => AttributeProxyBehavior::class,
            'attributes' => [
                'birthday' => 'profile.birthday',
                'wake_up' => 'profile.wake_up',
            ],
        ]);

        $user->birthday = time();
        self::assertNotNull($user->profile);
        self::assertEquals('7:00', $user->profile->wake_up);
        self::assertEquals('7:00', $user->wake_up);
    }

    public function testGetNullRelation()
    {
        $user = $this->createUser();
        $user->attachBehavior('attributeProxy', [
            'class' => AttributeProxyBehavior::class,
            'attributes' => [
                'birthday' => 'profile.birthday',
                'wake_up' => 'profile.wake_up',
            ],
        ]);

        self::assertEquals('7:00', $user->wake_up);
        self::assertNull($user->birthday);
    }

    public function testSetAttribute()
    {
        $user = $this->createUser();
        $user->attachBehavior('attributeProxy', [
            'class' => AttributeProxyBehavior::class,
            'attributes' => [
                'birthday' => 'profile.birthday',
            ],
        ]);

        $time = time();
        $user->birthday = $time;

        $profile = $user->profile;

        self::assertEquals($time, $profile->birthday);
        self::assertTrue($user->save(false));

        $profile->refresh();
        self::assertEquals($time, $profile->birthday);
        self::assertEquals($time, $profile->apiUser->birthday);

        $user->refresh();
        self::assertEquals($time, $user->apiUser->birthday);

        $this->clearTable(['user', 'user_profile', 'api_user']);
    }
}