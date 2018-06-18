<?php

namespace lav45\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;
use yii\helpers\ArrayHelper;
use yii\db\ActiveRecordInterface;

/**
 * Class PushBehavior
 *
 * ================ Example usage ================
 * public function behaviors()
 * {
 *      return [
 *          [
 *              'class' => PushBehavior::class,
 *              'relation' => 'apiUser',
 *              'attributes' => [
 *                  // Observe the change in the `status` attribute
 *                  // Writes the "value" in field `status` the relation model
 *                  'status',
 *
 *                  // Observe the change in the `status` attribute
 *                  // Writes the "value" in field `statusName` the relation model
 *                  'status' => 'statusName',
 *
 *                  // or
 *                  'status' => [  // Watch for changed attribute `status`
 *                      'field' => 'statusName', // and set this value in to the relation attribute `statusName`
 *                      'value' => 'status_name', // then get value from the attribute `status_name`
 *                  ],
 *
 *                  // Observe the change in the `status` attribute
 *                  [
 *                      'watch' => 'status', // if changed attribute `status`
 *                      // 'watch' => ['status', 'username'], // Watch for changes in a few fields
 *
 *                      'field' => 'statusName', // and set value in this relation attribute `statusName`
 *                      'value' => 'array.key', // then get value from the $this->array['key']
 *                      // 'value' => ['array', 'key'],
 *                      // 'value' => function($owner) {
 *                      //     return $owner->array['key'];
 *                      // },
 *                  ],
 *              ]
 *          ]
 *      ];
 * }
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
     *     [
     *         - watch: string|array => watch for changes in a few fields,
     *         - field: string => set value in this relation attribute,
     *         - value: string|array => get value from the attribute or path,
     *     ],
     * ]
     */
    private $attributes = [];
    /**
     * @var bool|\Closure whether to create related models
     * Can be passed to \Closure, then the user can instantiate the associated model
     * function () {
     *      $model = new Group(); // ActiveRecord extension
     *      return $model;
     * }
     */
    public $createRelation = true;
    /**
     * @var bool|\Closure whether to create related models
     * Can be passed to \Closure, then the user can instantiate the associated model
     * function (ActiveRecord $model) {
     *      return $model;
     * }
     */
    public $updateRelation = true;
    /**
     * @var bool|\Closure whether to delete related models
     * Can be passed to \Closure then the user will be able to decide how to unlink the link to the linked model
     * function (ActiveRecord $model) {
     *      // performed necessary actions related model
     *      $model->delete();
     * }
     */
    public $deleteRelation = true;

    /**
     * The method converts the value of the attributes field to a common form
     * @param array $attributes
     * Example: [
     *     'id',
     *     'login' => 'user_login',
     *     'updated_at' => [
     *         'field' => 'updatedAt',
     *         'value' => 'updated_at',
     *     ],
     *     [
     *         'watch' => 'status',
     *         // 'watch' => ['status', 'username'],
     *         'field' => 'statusName',
     *         'value' => 'attribute',
     *         // 'value' => 'array.key',
     *         // 'value' => ['array', 'key'],
     *         // 'value' => function($owner) {
     *         //     return $owner->array['key'];
     *         // },
     *     ],
     * ]
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = [];
        foreach ($attributes as $key => $value) {
            if (is_int($key) && is_string($value)) {
                $key = $value;
            }
            $this->attributes[] = [
                'watch' => is_string($key) ? $key : $value['watch'],
                'field' => is_string($value) ? $value : $value['field'],
                'value' => is_string($value) ? $key : $value['value'],
            ];
        }
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        $events[ActiveRecord::EVENT_AFTER_INSERT] = 'afterInsert';

        if ($this->updateRelation !== false) {
            $events[ActiveRecord::EVENT_AFTER_UPDATE] = 'afterUpdate';
        }
        if ($this->deleteRelation !== false) {
            $events[ActiveRecord::EVENT_BEFORE_DELETE] = 'beforeDelete';
        }
        return $events;
    }

    /**
     * Insert related model
     */
    final public function afterInsert()
    {
        foreach ($this->getItemsIterator() as $model) {
            if ($model === null) {
                if ($this->createRelation === false) {
                    continue;
                }
                if ($this->createRelation === true) {
                    $model = $this->createRelationModel();
                } elseif (is_callable($this->createRelation)) {
                    $model = call_user_func($this->createRelation);
                }
            }
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
    final public function afterUpdate(AfterSaveEvent $event)
    {
        if ($changedAttributes = $this->getChangedAttributes($event->changedAttributes)) {
            foreach ($this->getItemsIterator(true) as $item) {
                $this->updateItem($item, $changedAttributes);
                if ($this->updateRelation === true) {
                    $item->save(false);
                } elseif (is_callable($this->updateRelation)) {
                    call_user_func($this->updateRelation, $item);
                }
            }
        }
    }

    /**
     * @throws \Exception
     * @throws \Throwable
     */
    final public function beforeDelete()
    {
        foreach ($this->getItemsIterator(true) as $item) {
            if ($this->deleteRelation === true) {
                $item->delete();
            } elseif (is_callable($this->deleteRelation)) {
                call_user_func($this->deleteRelation, $item);
            }
        }
    }

    /**
     * @return ActiveRecordInterface
     */
    protected function createRelationModel()
    {
        $class = $this->owner->getRelation($this->relation)->modelClass;
        return new $class;
    }

    /**
     * @param bool $skip_empty
     * @return \Generator|ActiveRecordInterface[]
     */
    private function getItemsIterator($skip_empty = false)
    {
        $relation = $this->owner->getRelation($this->relation);

        if ($relation->multiple === true) {
            foreach ($relation->each() as $item) {
                yield $item;
            }
        } else {
            $item = $relation->one();
            if ($skip_empty === true) {
                if ($item) {
                    yield $item;
                }
            } else {
                yield $item;
            }
        }
    }

    /**
     * @param ActiveRecordInterface $model
     * @param array $attributes
     */
    protected function updateItem($model, $attributes)
    {
        foreach ($attributes as $attribute) {
            $model->{$attribute['field']} = ArrayHelper::getValue($this->owner, $attribute['value']);
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
}