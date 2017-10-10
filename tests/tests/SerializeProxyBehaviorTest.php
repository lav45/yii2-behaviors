<?php

namespace lav45\behaviors\tests\tests;

use lav45\behaviors\tests\models\News;

class SerializeProxyBehaviorTest extends \PHPUnit_Framework_TestCase
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

        self::assertEquals($model->tags, null);
        self::assertEquals($model->options, null);

        self::assertTrue($model->insert(false));
        self::assertEquals($model->update(false), 0);

        self::assertEquals($model->_tags, null);
        self::assertEquals($model->_options, null);

        $model->tags = $tags;
        $model->options = $options;
        self::assertTrue($model->save(false));

        self::assertEquals($model->_tags, json_encode($tags, 320));
        self::assertEquals($model->_options, json_encode($options, 320));

        $model = News::findOne($model->id);
        self::assertEquals($model->tags, $tags);
        self::assertEquals($model->options, $options);
    }

    public function testUpdate()
    {
        $data = $this->getDefaultData();
        $model = new News($data);

        self::assertTrue($model->save(false));

        $model->tags = ['new tag'];
        $model->options = null;

        self::assertTrue($model->isAttributeChanged('tags'));
        self::assertEquals($model->getOldAttribute('tags'), $data['tags']);
        self::assertTrue($model->isAttributeChanged('options'));
        self::assertEquals($model->getOldAttribute('options'), $data['options']);

        self::assertEquals($model->update(false), 1);

        /** @var News $model */
        $model = News::findOne($model->id);

        self::assertFalse($model->isAttributeChanged('tags'));
        self::assertFalse($model->isAttributeChanged('options'));

        self::assertEquals($model->tags, ['new tag']);
        self::assertEquals($model->options, null);
    }
}