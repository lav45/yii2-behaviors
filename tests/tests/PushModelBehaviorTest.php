<?php

namespace lav45\behaviors\tests\tests;

use Yii;
use lav45\behaviors\tests\models\PushModel;
use lav45\behaviors\tests\models\TargetModel;
use PHPUnit\Framework\TestCase;

class PushModelBehaviorTest extends TestCase
{
    /**
     * @throws \yii\db\Exception
     */
    public function tearDown()
    {
        TargetModel::$lastAction = null;
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
        $behavior = $model->getBehavior('push');
        if ($targetInsert) {
            $behavior->triggerInsert = $targetInsert;
        }

        $model->username = 'test';
        $model->save();

        $expected = [
            'id' => $model->id,
            'login' => $model->username,
        ];

        $this->assertEquals($expected, TargetModel::$lastAttributes);
        $this->assertEquals(TargetModel::ACTION_INSERT, TargetModel::$lastAction);

        // Update
        if ($targetUpdate) {
            $behavior->triggerUpdate = $targetUpdate;
        }

        $model->username = 'test 2';
        $model->save();

        $expected = [
            'id' => $model->id,
            'login' => $model->username,
        ];

        $this->assertEquals($expected, TargetModel::$lastAttributes);
        $this->assertEquals(TargetModel::ACTION_UPDATE, TargetModel::$lastAction);

        // Update without change
        $model->save();

        $expected = [
            'id' => $model->id,
            'login' => null,
        ];

        $this->assertEquals($expected, TargetModel::$lastAttributes);
        $this->assertEquals(TargetModel::ACTION_UPDATE, TargetModel::$lastAction);

        // Delete before
        if ($triggerBeforeDelete) {
            $behavior->triggerBeforeDelete = $triggerBeforeDelete;
        }
        $model->delete();

        $expected = [
            'id' => $model->id,
            'login' => $model->username,
        ];

        $this->assertEquals($expected, TargetModel::$lastAttributes);
        $this->assertEquals(TargetModel::ACTION_DELETE, TargetModel::$lastAction);

        // Delete after
        if ($triggerAfterDelete) {
            $behavior->triggerAfterDelete = $triggerAfterDelete;
        }
        $model->delete();

        $expected = [
            'id' => $model->id,
            'login' => $model->username,
        ];

        $this->assertEquals($expected, TargetModel::$lastAttributes);
        $this->assertEquals(TargetModel::ACTION_DELETE, TargetModel::$lastAction);
    }

    public function getCRUDModelDataProvider() {
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

    public function targetInsert(TargetModel $model) {
        $model->insert();
    }

    public function targetUpdate(TargetModel $model) {
        $model->update();
    }

    public function triggerBeforeDelete(TargetModel $model) {
        $model->beforeDelete();
    }

    public function triggerAfterDelete(TargetModel $model) {
        $model->afterDelete();
    }

    public function testCustomTargetClass()
    {
        $model = new PushModel();
        $model->username = 'test';

        /** @var \lav45\behaviors\PushModelBehavior $behavior */
        $behavior = $model->getBehavior('push');
        $behavior->targetClass = function () use (&$flag) {
            $flag = true;
            return new TargetModel;
        };

        $model->save();

        $expected = [
            'id' => $model->id,
            'login' => $model->username,
        ];

        $this->assertTrue($flag);
        $this->assertEquals($expected, TargetModel::$lastAttributes);
        $this->assertEquals(TargetModel::ACTION_INSERT, TargetModel::$lastAction);
    }

    /**
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function testDisabledTrigger()
    {
        // Create
        $model = new PushModel();
        /** @var \lav45\behaviors\PushModelBehavior $behavior */
        $behavior = $model->detachBehavior('push');

        $behavior->triggerInsert = false;
        $behavior->triggerUpdate = false;
        $behavior->triggerBeforeDelete = false;
        $behavior->triggerAfterDelete = false;

        $model->attachBehavior('push', $behavior);

        $model->username = 'test';
        $model->save();

        $this->assertEquals([], TargetModel::$lastAttributes);
        $this->assertNull(TargetModel::$lastAction);

        // Update
        $model->username = 'test 2';
        $model->save();

        $this->assertEquals([], TargetModel::$lastAttributes);
        $this->assertNull(TargetModel::$lastAction);

        // Delete
        $model->delete();

        $this->assertEquals([], TargetModel::$lastAttributes);
        $this->assertNull(TargetModel::$lastAction);
    }
}