<?php

namespace lav45\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;
use yii\base\InvalidConfigException;
use lav45\behaviors\traits\WatchAttributesTrait;

/**
 * Class PushModelBehavior
 * @package lav45\behaviors
 */
class PushModelBehavior extends Behavior
{
    use WatchAttributesTrait;

    /**
     * @var string|\Closure class namespace or custom function that will return the desired object
     */
    public $targetClass;
    /**
     * @var string|\Closure|bool
     */
    public $triggerInsert = 'insert';
    /**
     * @var string|\Closure|bool
     */
    public $triggerUpdate = 'update';
    /**
     * @var string|\Closure|bool
     */
    public $triggerDelete = 'delete';

    /**
     * @inheritdoc
     */
    public function events()
    {
        $events = [];
        if (false !== $this->triggerInsert) {
            $events[ActiveRecord::EVENT_AFTER_INSERT] = 'insert';
        }
        if (false !== $this->triggerUpdate) {
            $events[ActiveRecord::EVENT_AFTER_UPDATE] = 'update';
        }
        if (false !== $this->triggerDelete) {
            $events[ActiveRecord::EVENT_AFTER_DELETE] = 'delete';
        }
        return $events;
    }

    final public function insert()
    {
        $model = $this->getTargetModel();
        $this->updateModel($model, $this->attributes);
        $this->trigger($model, $this->triggerInsert);
    }

    final public function update(AfterSaveEvent $event)
    {
        if ($changedAttributes = $this->getChangedAttributes($event->changedAttributes)) {
            $model = $this->getTargetModel();
            $this->updateModel($model, $changedAttributes);
            $this->trigger($model, $this->triggerUpdate);
        }
    }

    final public function delete()
    {
        $model = $this->getTargetModel();
        $this->updateModel($model, $this->attributes);
        $this->trigger($model, $this->triggerDelete);
    }

    /**
     * @return object
     * @throws InvalidConfigException
     */
    protected function getTargetModel()
    {
        if (null === $this->targetClass) {
            throw new InvalidConfigException(__CLASS__ . '::$targetClass must be filled');
        }
        if ($this->targetClass instanceof \Closure) {
            return call_user_func($this->targetClass);
        }
        return new $this->targetClass;
    }

    /**
     * @param object $model
     * @param string|\Closure $triggerFunc
     */
    private function trigger($model, $triggerFunc)
    {
        if ($triggerFunc instanceof \Closure) {
            $triggerFunc($model);
        } else {
            $model->$triggerFunc();
        }
    }
}