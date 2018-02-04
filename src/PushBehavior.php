<?php

namespace lav45\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;
use yii\helpers\ArrayHelper;

/**
 * Class PushBehavior
 *
 * @package lav45\behaviors
 * @property ActiveRecord $owner
 */
class PushBehavior extends Behavior
{
    /**
     * @var string
     */
    public $relation;
    /**
     * @var array
     * [
     *      // Observe the change in the `status` attribute
     *      // Writes the "value" in field `statusName` the relation model
     *      'status' => 'statusName',
     *      // or
     *      'status' => [
     *          'field' => 'statusName',
     *          'value' => 'array.key'
     *          // 'value' => ['array', 'key'],
     *          // 'value' => function($owner) {
     *          //     return $owner->array['key'];
     *          // }
     *      ],
     *  ]
     */
    public $attributes = [];
    /**
     * @var bool|\Closure
     */
    public $enable = true;
    /**
     * @var bool|\Closure whether to delete related models
     *
     * function (ActiveRecord $model) {
     *      // performed necessary actions related model
     * }
     */
    public $deleteRelation = true;

    /**
     * @inheritdoc
     */
    public function events()
    {
        if ($this->isEnable() === false) {
            return [];
        }

        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete'
        ];
    }

    /**
     * @return bool
     */
    private function isEnable()
    {
        if (!is_bool($this->enable)) {
            $this->enable = (bool)call_user_func($this->enable);
        }
        return $this->enable;
    }

    /**
     * Update or insert related model
     */
    public final function afterInsert()
    {
        if (empty($models = $this->getRelation())) {
            $class = $this->owner->getRelation($this->relation)->modelClass;
            $models = [new $class];
        }
        if (is_array($models) === false) {
            $models = [$models];
        }
        foreach ($models as $model) {
            $this->updateItem($model, $this->attributes);

            if ($model->getIsNewRecord()) {
                if (!$model->beforeSave(true)) {
                    continue;
                }

                $this->owner->link($this->relation, $model);

                $changedAttributes = [];
                foreach ($model->attributes as $key => $value) {
                    if (null !== $value) {
                        $changedAttributes[$key] = null;
                    }
                }

                $model->afterSave(true, $changedAttributes);
            } else {
                $model->save(false);
                $this->owner->populateRelation($this->relation, $models);
            }
        }
    }

    /**
     * Update fields in related model
     * @param AfterSaveEvent $event
     */
    public final function afterUpdate(AfterSaveEvent $event)
    {
        if ($changedAttributes = $this->getChangedAttributes($event->changedAttributes)) {
            foreach ($this->getItemsIterator() as $item) {
                $this->updateItem($item, $changedAttributes);
                $item->save(false);
            }
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public final function beforeDelete()
    {
        if ($this->deleteRelation === false) {
            return;
        }

        foreach ($this->getItemsIterator() as $item) {
            if ($this->deleteRelation === true) {
                $item->delete();
            } elseif (is_callable($this->deleteRelation)) {
                call_user_func($this->deleteRelation, $item);
            }
        }
    }

    /**
     * @return \Generator|ActiveRecord[]
     */
    private function getItemsIterator()
    {
        $relation = $this->owner->getRelation($this->relation);

        if ($relation->multiple === true) {
            if ($this->owner->isRelationPopulated($this->relation)) {
                $items = $this->getRelation();
            } else {
                $items = $relation->each();
            }
            foreach ($items as $item) {
                yield $item;
            }
        } else {
            if ($item = $this->getRelation()) {
                yield $item;
            }
        }
    }

    /**
     * @param ActiveRecord $model
     * @param array $attributes
     */
    protected function updateItem($model, $attributes)
    {
        foreach ($attributes as $attribute => $target) {
            $field = is_array($target) ? $target['field'] : $target;
            $value = is_array($target) ? $target['value'] : $attribute;

            $model->{$field} = ArrayHelper::getValue($this->owner, $value);
        }
    }

    /**
     * @param array $changedAttributes
     * @return array
     */
    private function getChangedAttributes($changedAttributes)
    {
        return array_intersect_key($this->attributes, $changedAttributes);
    }

    /**
     * @return null|ActiveRecord|ActiveRecord[]
     */
    private function getRelation()
    {
        return $this->owner->{$this->relation};
    }
}
