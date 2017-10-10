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
        ];
    }

    public function testCreate()
    {
        $data = $this->getDefaultData();
        $model = new News($data);

        self::assertTrue($model->insert(false));
        self::assertEquals($model->update(false), 0);

        $model->is_active = 1;
        self::assertTrue($model->isAttributeChanged('is_active'));

        /** @var News $model */
        $model = News::findOne($model->id);

        self::assertEquals($model->title, $data['title']);
        self::assertEquals($model->meta, $data['meta']);
        self::assertEquals($model->is_active, $data['is_active']);
        self::assertEquals($model->_data, json_encode($data, 320));
    }

    public function testUpdate()
    {
        $data = $this->getDefaultData();
        $model = new News($data);

        self::assertTrue($model->save(false));

        $model->title = 'new title';
        $model->is_active = 1;

        $data = $this->getDefaultData();

        self::assertTrue($model->isAttributeChanged('title'));
        self::assertEquals($model->getOldAttribute('title'), $data['title']);

        self::assertFalse($model->isAttributeChanged('is_active', false));
        self::assertTrue($model->isAttributeChanged('is_active', true));

        self::assertFalse($model->getSerializeBehavior()->isAttributeChanged('not_fount_attribute'));
    }

    public function testGetDefaultValue()
    {
        $model = new News();

        self::assertEquals($model->is_active, true);
        self::assertEquals($model->title, null);
        self::assertEquals($model->defaultValue, 1);
        self::assertEquals($model->meta['keywords'], null);

        self::assertEquals($model->defaultFunc, null);
        self::assertTrue($model->save(false));
        self::assertEquals($model->defaultFunc, $model->id);

        self::assertEquals($model->_data, null);

        $model->is_active = null;
        $model->save(false);

        self::assertEquals($model->is_active, null);
        self::assertEquals($model->_data, '{"is_active":null}');
    }

    public function testIsset()
    {
        $model = new News();

        self::assertTrue(isset($model->defaultValue));
        unset($model->defaultValue);
        self::assertFalse(isset($model->defaultValue));
    }
}