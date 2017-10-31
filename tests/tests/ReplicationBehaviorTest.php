<?php

namespace lav45\behaviors\tests\tests;

use lav45\behaviors\tests\models\Page;
use lav45\behaviors\tests\models\PageReplication;

class ReplicationBehaviorTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $model = new Page();
        $model->text = 'text';
        $model->created_at = time();
        $model->updated_at = time();
        $model->detachBehaviors();
        $this->assertTrue($model->save(false)); // autoincrement id = 1

        $this->assertNull($model->pageReplication);

        $model = new Page();
        $model->text = 'text';
        $this->assertTrue($model->save(false)); // autoincrement id = 2

        $this->assertTrue($model->pageReplication instanceof PageReplication);
        $this->assertFalse($model->pageReplication->getIsNewRecord());

        $replicationModel = PageReplication::findOne($model->id);
        $this->assertNotNull($replicationModel);
        $this->assertEquals($replicationModel->id, $model->id);
        $this->assertEquals($replicationModel->description, $model->text);
        $this->assertEquals($replicationModel->updatedAt, $model->updated_at);
        $this->assertEquals($replicationModel->createdAt, $model->created_at);

        $this->assertTrue(Page::find()->count() > PageReplication::find()->count());
    }

    public function testUpdate()
    {
        $model = new Page();
        $model->text = 'text';
        $this->assertTrue($model->save(false));

        $model = Page::findOne($model->id);
        $this->assertNotNull($model);
        $this->assertTrue($model->save(false));

        $newText = 'update text';
        $model->text = $newText;
        $this->assertTrue($model->save(false));

        $this->assertEquals($model->pageReplication->description, $newText);
    }

    public function testDelete()
    {
        $model = new Page();
        $model->text = 'text';
        $this->assertTrue($model->save(false));

        $model = Page::findOne($model->id);
        $this->assertEquals($model->delete(), 1);

        $replicationModel = PageReplication::findOne($model->id);
        $this->assertNull($replicationModel);
    }
}