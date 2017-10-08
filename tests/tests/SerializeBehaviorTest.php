<?php

namespace lav45\behaviors\tests\tests;

use lav45\behaviors\tests\models\News;

class SerializeBehaviorTest extends \PHPUnit_Framework_TestCase
{
    protected function getDefaultData()
    {
        return [
            'title' => 'title',
            'meta' => [
                'title' => 'meta-title',
                'description' => 'meta-description',
                'keywords' => 'meta key words',
            ],
            'is_active' => true,
            'publish_date' => time(),
        ];
    }

    public function testCreate()
    {
        $data = $this->getDefaultData();

        $model = new News($data);
        self::assertTrue($model->insert(false));

        self::assertEquals($model->update(false), 0);

        /** @var News $model */
        $model = News::findOne($model->id);

        self::assertEquals($model->title, $data['title']);
        self::assertEquals($model->meta, $data['meta']);
        self::assertEquals($model->is_active, $data['is_active']);
        self::assertEquals($model->publish_date, $data['publish_date']);
        self::assertEquals($model->_data, json_encode($data, 320));
    }

    public function testUpdate()
    {
        /** @var News $model */
        $model = News::findOne(1);
        $model->title = 'new title';
        $model->is_active = 1;

        $data = $this->getDefaultData();

        self::assertTrue($model->isAttributeChanged('title'));
        self::assertEquals($model->getOldAttribute('title'), $data['title']);

        self::assertFalse($model->isAttributeChanged('is_active', false));
        self::assertTrue($model->isAttributeChanged('is_active', true));

        self::assertFalse($model->getSerializeBehavior()->isAttributeChanged('not_fount_attribute'));
    }
}