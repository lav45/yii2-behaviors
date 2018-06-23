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
     * @var string
     */
    public $triggerAfterInsert = 'insert';
    /**
     * @var string
     */
    public $triggerAfterUpdate = 'update';
    /**
     * @var string
     */
    public $triggerAfterDelete = 'delete';

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete'
        ];
    }

    final public function afterInsert()
    {
        $model = $this->getTargetModel();
        $this->updateModel($model, $this->attributes);
        $model->{$this->triggerAfterInsert}();
    }

    final public function afterUpdate(AfterSaveEvent $event)
    {
        if ($changedAttributes = $this->getChangedAttributes($event->changedAttributes)) {
            $model = $this->getTargetModel();
            $this->updateModel($model, $changedAttributes);
            $model->{$this->triggerAfterUpdate}();
        }
    }

    final public function afterDelete()
    {
        $model = $this->getTargetModel();
        $this->updateModel($model, $this->attributes);
        $model->{$this->triggerAfterDelete}();
    }

    /**
     * @return object
     * @throws InvalidConfigException
     */
    protected function getTargetModel()
    {
        if ($this->targetClass === null) {
            throw new InvalidConfigException(__CLASS__ . '::$targetClass must be filled');
        }
        if ($this->targetClass instanceof \Closure) {
            return call_user_func($this->targetClass);
        }
        return new $this->targetClass;
    }
}