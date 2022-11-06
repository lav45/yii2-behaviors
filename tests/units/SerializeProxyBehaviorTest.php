<?php

namespace lav45\behaviors\tests\units;

use lav45\behaviors\tests\models\News;
use PHPUnit\Framework\TestCase;

class SerializeProxyBehaviorTest extends TestCase
{
    protected function getDefaultData()
    {
        return [
            'tags' => ['tag1', 'tag2', 'tag3', 'tag4'],
            'options' => [
                'key1' => 'value1',
                'key2' => 'value2',
                'key3' => 'value3',
            ],
        ];
    }

    public function testCreate()
    {
        $data = $this->getDefaultData();
        $tags = $data['tags'];
        $options = $data['options'];

        $model = new News();

        $this->assertEquals($model->tags, null);
        $this->assertEquals($model->options, null);

        $this->assertTrue($model->insert(false));
        $this->assertEquals($model->update(false), 0);

        $this->assertEquals($model->_tags, null);
        $this->assertEquals($model->_options, null);

        $model->tags = $tags;
        $model->options = $options;
        $this->assertTrue($model->save(false));

        $this->assertEquals($model->_tags, json_encode($tags, 320));
        $this->assertEquals($model->_options, json_encode($options, 320));

        $model = News::findOne($model->id);
        $this->assertEquals($model->tags, $tags);
        $this->assertEquals($model->options, $options);
    }

    public function testUpdate()
    {
        $data = $this->getDefaultData();
        $model = new News($data);

        $this->assertTrue($model->save(false));

        $model->tags = ['new tag'];
        $model->options = null;

        $this->assertTrue($model->isAttributeChanged('tags'));
        $this->assertEquals($model->getOldAttribute('tags'), $data['tags']);
        $this->assertTrue($model->isAttributeChanged('options'));
        $this->assertEquals($model->getOldAttribute('options'), $data['options']);

        $this->assertEquals($model->update(false), 1);

        /** @var News $model */
        $model = News::findOne($model->id);

        $this->assertFalse($model->isAttributeChanged('tags'));
        $this->assertFalse($model->isAttributeChanged('options'));

        $this->assertEquals($model->tags, ['new tag']);
        $this->assertEquals($model->options, null);
    }
}