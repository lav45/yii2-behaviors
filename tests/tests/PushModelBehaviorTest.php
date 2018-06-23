<?php

namespace lav45\behaviors\tests\tests;

use lav45\behaviors\tests\models\PushModel;
use lav45\behaviors\tests\models\TargetModel;
use PHPUnit\Framework\TestCase;

class PushModelBehaviorTest extends TestCase
{
    public function testCRUDModel()
    {
        // Create
        $model = new PushModel();
        $model->username = 'test';
        $model->save();

        $expected = [
            'id' => $model->id,
            'login' => $model->username,
        ];

        $this->assertEquals($expected, TargetModel::$lastAttributes);
        $this->assertEquals(TargetModel::ACTION_INSERT, TargetModel::$lastAction);

        // Update
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

        // Delete
        $model->delete();

        $expected = [
            'id' => $model->id,
            'login' => $model->username,
        ];

        $this->assertEquals($expected, TargetModel::$lastAttributes);
        $this->assertEquals(TargetModel::ACTION_DELETE, TargetModel::$lastAction);
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
}