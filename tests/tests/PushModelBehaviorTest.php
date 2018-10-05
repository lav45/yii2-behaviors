<?php

namespace lav45\behaviors\tests\tests;

use Yii;
use lav45\behaviors\tests\models\PushModel;
use lav45\behaviors\tests\models\TargetModel;
use lav45\behaviors\tests\models\TargetARModel;
use lav45\behaviors\PushModelBehavior;
use PHPUnit\Framework\TestCase;

class PushModelBehaviorTest extends TestCase
{
    /**
     * @throws \yii\db\Exception
     */
    public function tearDown()
    {
        TargetModel::$lastAction = [];
        TargetModel::$lastAttributes = [];

        Yii::$app->getDb()->createCommand()
            ->truncateTable(PushModel::tableName())
            ->execute();
    }

    /**
     * @param $targetInsert null|array|\Closure
     * @param $targetUpdate null|array|\Closure
     * @param $triggerBeforeDelete null|array|\Closure
     * @param $triggerAfterDelete null|array|\Closure
     * @dataProvider getCRUDModelDataProvider
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function testCRUDModel($targetInsert, $targetUpdate, $triggerBeforeDelete, $triggerAfterDelete)
    {
        // Create
        $model = new PushModel();
        /** @var \lav45\behaviors\PushModelBehavior $behavior */
        $behavior = $model->attachBehavior('push', [
            '__class' => PushModelBehavior::class,
            'targetClass' => TargetModel::class,
            'triggerBeforeDelete' => 'beforeDelete',
            'triggerAfterDelete' => 'afterDelete',
            'attributes' => [
                'id' => ['watch' => true],
                'username' => 'login',
            ]
        ]);

        if ($targetInsert) {
            $behavior->triggerAfterInsert = $targetInsert;
        }

        $model->username = 'test';
        $model->save();

        $expected = [
            'id' => $model->id,
            'login' => $model->username,
        ];

        $this->assertEquals($expected, TargetModel::$lastAttributes);
        $this->assertEquals(TargetModel::ACTION_AFTER_INSERT, array_pop(TargetModel::$lastAction));

        // Update
        if ($targetUpdate) {
            $behavior->triggerAfterUpdate = $targetUpdate;
        }

        $model->username = 'test 2';
        $model->save();

        $expected = [
            'id' => $model->id,
            'login' => $model->username,
        ];

        $this->assertEquals($expected, TargetModel::$lastAttributes);
        $this->assertEquals(TargetModel::ACTION_AFTER_UPDATE, array_pop(TargetModel::$lastAction));

        // Update without change
        $model->save();

        $expected = [
            'id' => $model->id,
            'login' => null,
        ];

        $this->assertEquals($expected, TargetModel::$lastAttributes);
        $this->assertEquals(TargetModel::ACTION_AFTER_UPDATE, array_pop(TargetModel::$lastAction));

        // Update without trigger update method from TargetModel
        TargetModel::$lastAction = [];
        TargetModel::$lastAttributes = [];

        $behavior->setAttributes(['id', 'username' => 'login']);

        $model->save();

        $this->assertEquals([], TargetModel::$lastAttributes);
        $this->assertEquals([], TargetModel::$lastAction);

        $behavior->setAttributes([
            'id' => ['watch' => true],
            'username' => 'login',
        ]);

        // Delete
        if ($triggerBeforeDelete) {
            $behavior->triggerBeforeDelete = $triggerBeforeDelete;
        }
        if ($triggerAfterDelete) {
            $behavior->triggerAfterDelete = $triggerAfterDelete;
        }

        $model->delete();

        $expected = [
            'id' => $model->id,
            'login' => $model->username,
        ];

        $this->assertEquals($expected, TargetModel::$lastAttributes);
        $this->assertEquals(TargetModel::ACTION_AFTER_DELETE, array_pop(TargetModel::$lastAction));
        $this->assertEquals(TargetModel::ACTION_BEFORE_DELETE, array_pop(TargetModel::$lastAction));
    }

    public function getCRUDModelDataProvider()
    {
        return [
            'default triggers' => [
                null, null, null, null,
            ],
            'closure triggers' => [
                function (TargetModel $model) {
                    $model->insert();
                },
                function (TargetModel $model) {
                    $model->update();
                },
                function (TargetModel $model) {
                    $model->beforeDelete();
                },
                function (TargetModel $model) {
                    $model->afterDelete();
                },
            ],
            'array triggers' => [
                [$this, 'targetInsert'],
                [$this, 'targetUpdate'],
                [$this, 'triggerBeforeDelete'],
                [$this, 'triggerAfterDelete'],
            ],
        ];
    }

    public function targetInsert(TargetModel $model)
    {
        $model->insert();
    }

    public function targetUpdate(TargetModel $model)
    {
        $model->update();
    }

    public function triggerBeforeDelete(TargetModel $model)
    {
        $model->beforeDelete();
    }

    public function triggerAfterDelete(TargetModel $model)
    {
        $model->afterDelete();
    }

    public function testCustomTargetClass()
    {
        $model = new PushModel();
        $model->username = 'test';

        $model->attachBehavior('push', [
            '__class' => PushModelBehavior::class,
            'targetClass' => function () use (&$flag) {
                $flag = true;
                return new TargetModel;
            },
            'triggerBeforeDelete' => 'beforeDelete',
            'triggerAfterDelete' => 'afterDelete',
            'attributes' => [
                'id' => ['watch' => true],
                'username' => 'login',
            ]
        ]);

        $model->save();

        $expected = [
            'id' => $model->id,
            'login' => $model->username,
        ];

        $this->assertTrue($flag);
        $this->assertEquals($expected, TargetModel::$lastAttributes);
        $this->assertEquals(TargetModel::ACTION_AFTER_INSERT, array_pop(TargetModel::$lastAction));
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function testDisabledTrigger()
    {
        // Create
        $model = new PushModel();
        $model->attachBehavior('push', [
            '__class' => PushModelBehavior::class,
            'triggerAfterInsert' => null,
            'triggerAfterUpdate' => null,
            'triggerBeforeDelete' => null,
            'triggerAfterDelete' => null,
        ]);

        $model->username = 'test';
        $model->save();

        $this->assertEquals([], TargetModel::$lastAttributes);
        $this->assertEquals([], TargetModel::$lastAction);

        // Update
        $model->username = 'test 2';
        $model->save();

        $this->assertEquals([], TargetModel::$lastAttributes);
        $this->assertEquals([], TargetModel::$lastAction);

        // Delete
        $model->delete();

        $this->assertEquals([], TargetModel::$lastAttributes);
        $this->assertEquals([], TargetModel::$lastAction);
    }

    public function testTargetARModel()
    {
        // Create
        $model = new PushModel();
        $model->attachBehavior('push', [
            '__class' => PushModelBehavior::class,
            'targetClass' => function () use ($model) {
                return TargetARModel::findOne($model->id) ?: new TargetARModel();
            },
            'attributes' => [
                'id' => ['watch' => true],
                'username',
            ]
        ]);

        $username = 'test';
        $model->username = $username;
        $model->save();

        $target_model = TargetARModel::findOne($model->id);
        $this->assertNotNull($target_model);
        $this->assertEquals($username, $target_model->username);

        // Update
        $username = 'test 2';
        $model->username = $username;
        $model->save();

        $target_model->refresh();
        $this->assertEquals($username, $target_model->username);

        // Delete
        $model->delete();
        $this->assertFalse($target_model->refresh());
    }
}