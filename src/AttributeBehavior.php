<?php

namespace lav45\behaviors;

use yii\base\Behavior;

/**
 * Class AttributeBehavior
 * @package lav45\behaviors
 */
abstract class AttributeBehavior extends Behavior
{
    /**
     * @var array flip target attributes
     */
    protected $attributes = [];

    /**
     * @param string $name
     * @return mixed
     */
    abstract public function getAttribute($name);

    /**
     * @param string $name
     * @param mixed $value
     */
    abstract public function setAttribute($name, $value);

    /**
     * @param string $name
     * @return bool
     */
    public function hasAttribute($name)
    {
        return isset($this->attributes[$name]) || array_key_exists($name, $this->attributes);
    }

    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true)
    {
        return $this->hasAttribute($name) || parent::canGetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        return $this->hasAttribute($name) || parent::canSetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        return $this->hasAttribute($name) ? $this->getAttribute($name) : parent::__get($name);
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        if ($this->hasAttribute($name)) {
            $this->setAttribute($name, $value);
        } else {
            parent::__set($name, $value);
        }
    }
}