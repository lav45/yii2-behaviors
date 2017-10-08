<?php
/**
 * Created by PhpStorm.
 * User: lav45
 * Date: 04.10.17
 * Time: 0:42
 */

namespace lav45\behaviors\tests\tests;

use lav45\behaviors\tests\models\Page;
use lav45\behaviors\tests\models\PageReplication;

class ReplicationBehaviorTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $model = new Page();
        $model->id = 1;
        $model->text = 'text';
        $this->assertTrue($model->save());

        $replicationModel = PageReplication::findOne($model->id);
        $this->assertNotNull($replicationModel);
        $this->assertEquals($replicationModel->description, $model->text);
        $this->assertEquals($replicationModel->updatedAt, $model->updated_at);
        $this->assertEquals($replicationModel->createdAt, $model->created_at);
    }

    public function testUpdate()
    {
        $model = Page::findOne(1);
        $this->assertNotNull($model);
        $this->assertTrue($model->save(false));

        $newText = 'update text';
        $model->text = $newText;
        $this->assertTrue($model->save(false));

        $this->assertEquals($model->pageReplication->description, $newText);
    }

    public function testDelete()
    {
        $model = Page::findOne(1);
        $this->assertEquals($model->delete(), 1);

        $replicationModel = PageReplication::findOne(1);
        $this->assertNull($replicationModel);
    }
}