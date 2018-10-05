<?php

namespace lav45\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;
use yii\base\InvalidConfigException;
use lav45\behaviors\traits\WatchAttributesTrait;

/**
 * Class PushModelBehavior
 * @package lav45\behaviors
 * @property-write string|array|callable|null $triggerAfterInsert
 * @property-write string|array|callable|null $triggerAfterUpdate
 * @property-write string|array|callable|null $triggerBeforeDelete
 * @property-write string|array|callable|null $triggerAfterDelete
 */
class PushModelBehavior extends Behavior
{
    use WatchAttributesTrait;

    /**
     * Class namespace or custom function that will return the desired object
     * @var string|array|callable
     */
    public $targetClass;
    /**
     * @var array [
     *      ActiveRecord::EVENT_AFTER_INSERT => 'method_save',
     *      ActiveRecord::EVENT_AFTER_UPDATE => function($model, AfterSaveEvent $event) { },
     *      ActiveRecord::EVENT_AFTER_DELETE => [$this, 'triggerAfterDelete'],
     * ]
     */
    public $events = [
        ActiveRecord::EVENT_AFTER_INSERT => 'insert',
        ActiveRecord::EVENT_AFTER_UPDATE => 'update',
        ActiveRecord::EVENT_AFTER_DELETE => 'delete',
    ];

    /**
     * The method that will be called from the target model on the event [[ActiveRecord::EVENT_AFTER_INSERT]]
     * @param $data string|array|callable|null
     * @deprecated will be removed in the 0.7 version
     */
    public function setTriggerAfterInsert($data)
    {
        $this->events[ActiveRecord::EVENT_AFTER_INSERT] = $data;
    }

    /**
     * The method that will be called from the target model on the event [[ActiveRecord::EVENT_AFTER_UPDATE]]
     * @param $data string|array|callable|null
     * @deprecated will be removed in the 0.7 version
     */
    public function setTriggerAfterUpdate($data)
    {
        $this->events[ActiveRecord::EVENT_AFTER_UPDATE] = $data;
    }

    /**
     * The method that will be called from the target model on the event [[ActiveRecord::EVENT_BEFORE_DELETE]]
     * by default, nothing happens
     * @param $data string|array|callable|null
     * @deprecated will be removed in the 0.7 version
     */
    public function setTriggerBeforeDelete($data)
    {
        $this->events[ActiveRecord::EVENT_BEFORE_DELETE] = $data;
    }

    /**
     * The method that will be called from the target model on the event [[ActiveRecord::EVENT_AFTER_DELETE]]
     * @param $data string|array|callable|null
     * @deprecated will be removed in the 0.7 version
     */
    public function setTriggerAfterDelete($data)
    {
        $this->events[ActiveRecord::EVENT_AFTER_DELETE] = $data;
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        $events = array_filter($this->events);
        if (empty($events)) {
            return [];
        }

        $events = array_keys($events);
        $values = array_fill(0, count($events), 'runTrigger');
        $events = array_combine($events, $values);

        return $events;
    }

    /**
     * @param \yii\base\Event $event
     * @throws InvalidConfigException
     */
    public function runTrigger($event)
    {
        if ($event instanceof AfterSaveEvent) {
            $attributes = $this->getChangedAttributes($event->changedAttributes);
        } else {
            $attributes = $this->attributes;
        }
        if ($attributes) {
            $model = Yii::createObject($this->targetClass);
            $this->updateModel($model, $attributes);
            $this->trigger($model, $this->events[$event->name]);
        }
    }

    /**
     * @param object $model
     * @param string|array|\Closure $triggerFunc
     */
    private function trigger($model, $triggerFunc)
    {
        if (is_string($triggerFunc)) {
            call_user_func([$model, $triggerFunc]);
        } else {
            call_user_func($triggerFunc, $model);
        }
    }
}