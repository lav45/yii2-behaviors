<?php

namespace lav45\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;
use yii\helpers\ArrayHelper;

/**
 * Class ReplicationBehavior
 * @package lav45\behaviors
 *
 * @property ActiveRecord $owner
 */
class ReplicationBehavior extends Behavior
{
    /**
     * @var string
     */
    public $relation;
    /**
     * @var array
     * [
     *      'field-from-owner-model-1' => 'field-from-tracked-model-1',
     *      'field-from-owner-model-2' => [
     *          'field' => 'field-from-tracked-model-2',
     *          'value' => function($ownerModel) {
     *              return $ownerModel->getFieldValue();
     *          }
     *      ],
     *      'field-from-owner-model-3' => [
     *          'field' => 'field-from-tracked-model-3',
     *          'value' => 'relation.attribute'
     *      ],
     * ]
     */
    public $attributes;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    /**
     * @param AfterSaveEvent $event
     */
    public function afterSave(AfterSaveEvent $event)
    {
        $attributeChanged = $this->getChangedAttributes($event->changedAttributes);

        if (empty($attributeChanged)) {
            return;
        }

        $model = $this->getRelationModel();

        $this->updateModel($model, $attributeChanged);

        $model->save(false);
    }

    /**
     * @throws \Exception
     * @throws \yii\db\StaleObjectException
     */
    public function beforeDelete()
    {
        if($model = $this->getRelation()) {
            $model->delete();
        }
    }

    /**
     * @param array $changedAttributes
     * @return array
     */
    protected function getChangedAttributes($changedAttributes)
    {
        return array_intersect_key($this->attributes, $changedAttributes);
    }

    /**
     * @param ActiveRecord $model
     * @param array $attributeChanged
     */
    protected function updateModel($model, $attributeChanged)
    {
        foreach ($attributeChanged as $field => $source) {
            $source_field = (is_array($source)) ? $source['field'] : $source;
            $source_value = (is_array($source)) ? $source['value'] : $field;

            $model->{$source_field} = ArrayHelper::getValue($this->owner, $source_value);
        }
    }

    /**
     * @return ActiveRecord
     */
    protected function getRelationModel()
    {
        if ($model = $this->getRelation()) {
            return $model;
        }

        $relationClass = $this->owner->getRelation($this->relation)->modelClass;
        return new $relationClass;
    }

    /**
     * @return ActiveRecord
     */
    protected function getRelation()
    {
        return $this->owner->{$this->relation};
    }
}