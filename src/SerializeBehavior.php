<?php

namespace lav45\behaviors;

use yii\db\ActiveRecord;

/**
 * Class SerializeBehavior
 * @package lav45\behaviors
 * @property ActiveRecord $owner
 */
class SerializeBehavior extends AttributeBehavior
{
    /**
     * @var string
     */
    public $targetAttribute;
    /**
     * @var \Closure|array|string
     * @see Json::encode()
     */
    public $encode = '\yii\helpers\Json::encode';
    /**
     * @var \Closure|array|string
     * @see Json::decode()
     */
    public $decode = '\yii\helpers\Json::decode';
    /**
     * @var array
     */
    private $data;
    /**
     * @var array
     */
    private $_oldData;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'loadData',
            ActiveRecord::EVENT_BEFORE_INSERT => 'saveData',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'saveData',
        ];
    }

    public function loadData()
    {
        $this->data = $this->decode($this->owner[$this->targetAttribute]);
        $this->_oldData = $this->data;
    }

    public function saveData()
    {
        $this->_oldData = $this->data;
        $this->owner[$this->targetAttribute] = $this->encode($this->data);
    }

    /**
     * @param string $name
     * @param bool $identical
     * @return bool
     */
    public function isAttributeChanged($name, $identical = true)
    {
        if (isset($this->data[$name], $this->_oldData[$name])) {
            if ($identical) {
                return $this->data[$name] !== $this->_oldData[$name];
            } else {
                return $this->data[$name] != $this->_oldData[$name];
            }
        } else {
            return isset($this->data[$name]) || isset($this->_oldData[$name]);
        }
    }

    public function getOldAttribute($name)
    {
        return isset($this->_oldData[$name]) ? $this->_oldData[$name] : null;
    }

    /**
     * @param array $value
     * @return string
     */
    protected function encode($value)
    {
        return call_user_func($this->encode, $value);
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function decode($value)
    {
        return call_user_func($this->decode, $value);
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function getValue($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    protected function setValue($name, $value)
    {
        $this->data[$name] = $value;
    }
}