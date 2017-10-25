<?php

namespace lav45\behaviors;

use yii\db\ActiveRecord;
use lav45\behaviors\contracts\AttributeChangeInterface;
use lav45\behaviors\contracts\OldAttributeInterface;

/**
 * Class SerializeBehavior
 * @package lav45\behaviors
 *
 * @property ActiveRecord $owner
 * @property-write array $attributes
 */
class SerializeBehavior extends AttributeBehavior implements AttributeChangeInterface, OldAttributeInterface
{
    use SerializeTrait;

    use ChangeAttributesTrait;

    /**
     * @var string
     */
    public $storageAttribute;

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
        $this->data = $this->decode($this->owner[$this->storageAttribute]);
        $this->oldData = $this->data;
    }

    public function saveData()
    {
        if ($this->data !== $this->oldData) {
            $this->oldData = $this->data;
            $this->owner[$this->storageAttribute] = $this->encode($this->data);
        }
    }

    /**
     * @param array $data
     */
    public function setAttributes(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_int($key)) {
                $this->attributes[$value] = null;
            } else {
                $this->attributes[$key] = $value;
            }
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getAttribute($name)
    {
        if (isset($this->data[$name]) || array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        $value = $this->attributes[$name];

        if ($value instanceof \Closure) {
            return call_user_func($value);
        }

        return $value;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute($name, $value)
    {
        $this->data[$name] = $value;
    }
}
