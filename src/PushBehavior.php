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
     * @var string target relation name
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
     *          'value' => 'array.key',
     *          // 'value' => ['array', 'key'],
     *          // 'value' => function($owner) {
     *          //     return $owner->array['key'];
     *          // },
     *      ],
     *      [
     *          'watch' => 'status',
     *          // 'watch' => ['status', 'username'],
     *          'field' => 'statusName',
     *          'value' => 'array.key',
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
     * @var bool local state of the variable `$enable`
     */
    private $isEnable = true;


    public function init()
    {
        $this->initAttributes();
    }

    protected function initAttributes()
    {
        $result = [];
        foreach ($this->attributes as $key => $value) {
            if (is_int($key) && is_string($value)) {
                $key = $value;
            }
            $result[] = [
                'watch' => is_string($key) ? $key : $value['watch'],
                'field' => is_string($value) ? $value : $value['field'],
                'value' => is_string($value) ? $key : $value['value'],
            ];
        }
        $this->attributes = $result;
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete'
        ];
    }

    public function beforeSave()
    {
        if (is_bool($this->enable)) {
            $this->isEnable = $this->enable;
        } else {
            $this->isEnable = (bool)call_user_func($this->enable);
        }
    }

    /**
     * Update or insert related model
     */
    public final function afterInsert()
    {
        if ($this->isEnable === false) {
            return;
        }
        foreach ($this->findOrCreateRelations() as $model) {
            $this->updateItem($model, $this->attributes);
            if ($model->getIsNewRecord()) {
                $this->owner->link($this->relation, $model);
            } else {
                $model->save(false);
            }
        }
    }

    /**
     * Update fields in related model
     * @param AfterSaveEvent $event
     */
    public final function afterUpdate(AfterSaveEvent $event)
    {
        if ($this->isEnable === false) {
            return;
        }
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
     * @return ActiveRecord[]
     */
    private function findOrCreateRelations()
    {
        $models = $this->getRelation();
        if (empty($models)) {
            $class = $this->owner->getRelation($this->relation)->modelClass;
            return [new $class];
        }
        if (!is_array($models)) {
            return [$models];
        }
        return $models;
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
        foreach ($attributes as $attribute) {
            $field = $attribute['field'];
            $value = $attribute['value'];

            $model->{$field} = ArrayHelper::getValue($this->owner, $value);
        }
    }

    /**
     * @param array $changedAttributes
     * @return array
     */
    private function getChangedAttributes($changedAttributes)
    {
        $result = [];
        foreach ($this->attributes as $attribute) {
            $watch = $attribute['watch'];
            if (is_array($watch)) {
                foreach ($watch as $item) {
                    if (isset($changedAttributes[$item]) || array_key_exists($item, $changedAttributes)) {
                        $result[] = $attribute;
                        break;
                    }
                }
            } else {
                if (isset($changedAttributes[$watch]) || array_key_exists($watch, $changedAttributes)) {
                    $result[] = $attribute;
                }
            }
        }
        return $result;
    }

    /**
     * @return null|ActiveRecord|ActiveRecord[]
     */
    private function getRelation()
    {
        return $this->owner->{$this->relation};
    }
}
