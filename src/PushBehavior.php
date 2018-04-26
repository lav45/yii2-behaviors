<?php

namespace lav45\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\ActiveRecordInterface;
use yii\db\AfterSaveEvent;
use yii\helpers\ArrayHelper;

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
 *                      'field' => 'statusName', // and set this value in to the relation attribute `statusName`
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
     *     'id',
     *     'login' => 'user_login',
     *     'updated_at' => [
     *         'field' => 'updatedAt',
     *         'value' => 'updated_at',
     *     ],
     *     [
     *         // 'watch' => 'status',
     *         'watch' => ['status', 'username'],
     *         'field' => 'statusName',
     *         'value' => 'array.key',
     *         // 'value' => ['array', 'key'],
     *         // 'value' => function($owner) {
     *         //     return $owner->array['key'];
     *         // },
     *     ],
     * ]
     */
    public $attributes = [];
    /**
     * @var bool|\Closure whether to delete related models
     *
     * function (ActiveRecord $model) {
     *      // performed necessary actions related model
     * }
     */
    public $deleteRelation = true;
    /**
     * @var bool
     */
    public $createRelation = true;


    /**
     * Initializes the object.
     */
    public function init()
    {
        $this->initAttributes();
    }

    /**
     * The method leads the value of the field 'attributes' to the general form
     */
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
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete'
        ];
    }

    /**
     * Update or insert related model
     */
    final public function afterInsert()
    {
        foreach ($this->getItemsIterator() as $model) {
            if ($model === null && $this->createRelation === true) {
                $model = $this->createRelationModel();
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
    final public function beforeDelete()
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
     * @return ActiveRecordInterface
     */
    protected function createRelationModel()
    {
        $class = $this->owner->getRelation($this->relation)->modelClass;
        return new $class;
    }

    /**
     * @return \Generator|ActiveRecordInterface[]
     */
    private function getItemsIterator()
    {
        $relation = $this->owner->getRelation($this->relation);

        if ($relation->multiple === true) {
            foreach ($relation->each() as $item) {
                yield $item;
            }
        } else {
            yield $relation->one();
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