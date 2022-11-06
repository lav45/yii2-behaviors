<?php

namespace lav45\behaviors\tests\units;

use lav45\behaviors\tests\models\News;
use PHPUnit\Framework\TestCase;

class SerializeBehaviorTest extends TestCase
{
    protected function getDefaultData()
    {
        return [
            'description' => 'description',
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

        $this->assertTrue($model->insert(false));
        $this->assertEquals($model->update(false), 0);

        $model->is_active = 1;
        $this->assertTrue($model->isAttributeChanged('is_active'));

        /** @var News $model */
        $model = News::findOne($model->id);

        $this->assertEquals($model->description, $data['description']);
        $this->assertEquals($model->meta, $data['meta']);
        $this->assertEquals($model->is_active, $data['is_active']);

        $_data = $data;
        $_data['meta_keywords'] = $data['meta']['keywords'];
        $_data = json_encode($_data, 320);

        $this->assertEquals($model->_data, $_data);
    }

    public function testUpdate()
    {
        $data = $this->getDefaultData();
        $model = new News($data);

        $new_title = 'set title';
        $model->title = $new_title;
        $this->assertTrue($model->isAttributeChanged('title'));

        $this->assertEquals($model->getOldAttribute('title'), null);
        $this->assertTrue($model->save(false));
        $this->assertEquals($model->getOldAttribute('title'), $new_title);

        $model->description = 'new description';
        $model->is_active = 1;

        $data = $this->getDefaultData();

        $this->assertTrue($model->isAttributeChanged('description'));
        $this->assertEquals($model->getOldAttribute('description'), $data['description']);

        $this->assertFalse($model->isAttributeChanged('is_active', false));
        $this->assertTrue($model->isAttributeChanged('is_active', true));

        $this->assertEquals($model->getAttribute('not_fount_attribute'), null);
        $this->assertEquals($model->getOldAttribute('not_fount_attribute'), null);
        $this->assertFalse($model->isAttributeChanged('not_fount_attribute'));
    }

    public function testGetDefaultValue()
    {
        $model = new News();

        $this->assertEquals($model->is_active, true);
        $this->assertEquals($model->getAttribute('is_active'), true);

        $this->assertEquals($model->description, null);
        $this->assertEquals($model->defaultValue, 1);
        $this->assertEquals($model->meta['keywords'], null);

        $this->assertEquals($model->defaultFunc, null);
        $this->assertTrue($model->save(false));
        $this->assertEquals($model->defaultFunc, $model->id);

        $this->assertEquals($model->_data, null);
        $this->assertEquals($model->getAttribute('id'), $model->id);

        $model->is_active = null;
        $model->save(false);

        $this->assertEquals($model->is_active, null);
        $this->assertEquals($model->_data, '{"meta_keywords":null,"is_active":null}');
    }

    public function testIsset()
    {
        $model = new News();

        $this->assertTrue(isset($model->defaultValue));
        unset($model->defaultValue);
        $this->assertFalse(isset($model->defaultValue));
    }

    public function testDoableSetStorageAttribute()
    {
        $data = $this->getDefaultData();
        $model = new News($data);

        $model->save(false);

        $this->assertEquals($model->meta_keywords, $data['meta']['keywords']);
    }
}