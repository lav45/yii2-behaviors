<?php

namespace lav45\behaviors;

use lav45\behaviors\contracts\AttributeChangeInterface;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

/**
 * Class AttributeProxyBehavior
 * @package lav45\behaviors
 *
 * @property-write array $attributes
 * @property ActiveRecord $owner
 */
class AttributeProxyBehavior extends AttributeBehavior implements AttributeChangeInterface
{
    /**
     * @var bool
     */
    public $createRelation = true;
    /**
     * @var array
     */
    public $createExtraColumns = [];
    /**
     * @var bool
     */
    public $deleteRelation = true;
    /**
     * @var array
     */
    private $changeRelation = [];

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
     * @param array $data [
     *     'virtual_attribute' => 'relation.attribute',
     *     'birthday' => 'profile.birthday',
     *     'user_email' => 'profile.email',
     * ]
     */
    public function setAttributes(array $data)
    {
        $this->attributes = [];
        foreach ($data as $key => $value) {
            $this->attributes[$key] = explode('.', $value);
        }
    }

    /**
     * Insert or Update related model
     */
    final public function afterSave()
    {
        foreach ($this->changeRelation as $relation) {
            $model = $this->getRelationModel($relation);
            if ($model === null) {
                continue;
            }
            if ($model->getIsNewRecord()) {
                $this->owner->link($relation, $model, $this->createExtraColumns);
            } else {
                $model->save(false);
            }
        }
        $this->changeRelation = [];
    }

    /**
     * Delete related model
     */
    final public function beforeDelete()
    {
        $deleted = [];
        foreach ($this->attributes as $attribute) {
            $relation = $attribute[0];
            if (isset($deleted[$relation])) {
                continue;
            }
            $deleted[$relation] = true;
            $model = $this->getRelationModel($relation);
            if ($model === null) {
                continue;
            }
            if ($model->getIsNewRecord() === false) {
                $this->owner->unlink($relation, $model, $this->deleteRelation);
            }
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getAttribute($name)
    {
        list($relation, $attribute) = $this->attributes[$name];
        $model = $this->getRelationModel($relation);
        if ($model === null) {
            return null;
        }
        return $model->{$attribute};
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute($name, $value)
    {
        list($relation, $attribute) = $this->attributes[$name];
        $model = $this->getRelationModel($relation);
        if ($model === null) {
            return;
        }
        $this->changeRelation[$relation] = $relation;
        $model->{$attribute} = $value;
    }

    /**
     * @param string $name
     * @param bool $identical
     * @return bool
     */
    public function isAttributeChanged($name, $identical = true)
    {
        list($relation, $attribute) = $this->attributes[$name];
        $model = $this->getRelationModel($relation);
        if ($model === null) {
            return false;
        }
        return $model->isAttributeChanged($attribute, $identical);
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getOldAttribute($name)
    {
        list($relation, $attribute) = $this->attributes[$name];
        $model = $this->getRelationModel($relation);
        if ($model === null) {
            return false;
        }
        return $model->getOldAttribute($attribute);
    }

    /**
     * @param string $relation
     * @return ActiveRecord|null
     * @throws InvalidConfigException
     */
    protected function getRelationModel($relation)
    {
        if ($this->owner->isRelationPopulated($relation)) {
            return $this->owner->__get($relation);
        }

        /** @var \yii\db\ActiveQuery $query */
        $query = $this->owner->getRelation($relation);

        if ($query === null) {
            throw new InvalidConfigException("Relation '{$relation}' not found.");
        }
        if ($query->multiple === true) {
            throw new InvalidConfigException("Multiple relations are not supported.");
        }

        /** @var ActiveRecord $model */
        $model = $query->one();

        if ($model === null && $this->createRelation === true) {
            $class = $query->modelClass;
            /** @var ActiveRecord $model */
            $model = new $class;

            if (method_exists($model, 'loadDefaultValues')) {
                $model->loadDefaultValues();
            }
        }

        $this->owner->populateRelation($relation, $model);

        return $model;
    }
}