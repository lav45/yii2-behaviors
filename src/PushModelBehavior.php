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
     * @var string|array|\Closure|null
     */
    public $triggerInsert = 'insert';
    /**
     * @var string|array|\Closure|null
     */
    public $triggerUpdate = 'update';
    /**
     * @var string|array|\Closure|null
     */
    public $triggerBeforeDelete = 'beforeDelete';
    /**
     * @var string|array|\Closure|null
     */
    public $triggerAfterDelete = 'afterDelete';

    /**
     * @inheritdoc
     */
    public function events()
    {
        $events = [];
        if (!empty($this->triggerInsert)) {
            $events[ActiveRecord::EVENT_AFTER_INSERT] = 'insert';
        }
        if (!empty($this->triggerUpdate)) {
            $events[ActiveRecord::EVENT_AFTER_UPDATE] = 'update';
        }
        if (!empty($this->triggerBeforeDelete)) {
            $events[ActiveRecord::EVENT_BEFORE_DELETE] = 'beforeDelete';
        }
        if (!empty($this->triggerAfterDelete)) {
            $events[ActiveRecord::EVENT_AFTER_DELETE] = 'afterDelete';
        }
        return $events;
    }

    /**
     * @throws InvalidConfigException
     */
    final public function insert()
    {
        $model = $this->getTargetModel();
        $this->updateModel($model, $this->attributes);
        $this->trigger($model, $this->triggerInsert);
    }

    /**
     * @param AfterSaveEvent $event
     * @throws InvalidConfigException
     */
    final public function update(AfterSaveEvent $event)
    {
        if ($changedAttributes = $this->getChangedAttributes($event->changedAttributes)) {
            $model = $this->getTargetModel();
            $this->updateModel($model, $changedAttributes);
            $this->trigger($model, $this->triggerUpdate);
        }
    }

    /**
     * @throws InvalidConfigException
     */
    final public function beforeDelete()
    {
        $model = $this->getTargetModel();
        $this->updateModel($model, $this->attributes);
        $this->trigger($model, $this->triggerBeforeDelete);
    }

    /**
     * @throws InvalidConfigException
     */
    final public function afterDelete()
    {
        $model = $this->getTargetModel();
        $this->updateModel($model, $this->attributes);
        $this->trigger($model, $this->triggerAfterDelete);
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
        if (is_callable($this->targetClass)) {
            return call_user_func($this->targetClass);
        }
        return new $this->targetClass;
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