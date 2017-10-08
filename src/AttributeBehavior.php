<?php

namespace lav45\behaviors;

use yii\base\Behavior;

/**
 * Class AttributeBehavior
 * @package lav45\behaviors
 *
 * @property-write array $attributes
 */
abstract class AttributeBehavior extends Behavior
{
    /**
     * @var array flip target attributes
     */
    private $_attributes = [];

    /**
     * @param array $data
     */
    public function setAttributes(array $data)
    {
        if (!empty($data)) {
            $this->_attributes = array_flip($data);
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    abstract protected function getValue($name);

    /**
     * @param string $name
     * @param mixed $value
     */
    abstract protected function setValue($name, $value);

    /**
     * @param string $name
     * @return bool
     */
    public function isAttribute($name)
    {
        return array_key_exists($name, $this->_attributes);
    }

    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true)
    {
        return $this->isAttribute($name) || parent::canGetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        return $this->isAttribute($name) || parent::canSetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        return $this->isAttribute($name) ? $this->getValue($name) : parent::__get($name);
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        if ($this->isAttribute($name)) {
            $this->setValue($name, $value);
        } else {
            parent::__set($name, $value);
        }
    }
}