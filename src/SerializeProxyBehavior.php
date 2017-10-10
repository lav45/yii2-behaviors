<?php

namespace lav45\behaviors;

use yii\db\ActiveRecord;

/**
 * Class SerializeProxyBehavior
 * @package lav45\behaviors
 * @property ActiveRecord $owner
 * @property-write array $attributes
 */
class SerializeProxyBehavior extends AttributeBehavior
{
    use SerializeTrait;

    use ChangeAttributesTrait;

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
        foreach ($this->attributes as $target => $storage) {
            $this->data[$target] = $this->decode($this->owner[$storage]);
            $this->oldData[$target] = $this->data[$target];
        }
    }

    public function saveData()
    {
        foreach ($this->attributes as $target => $storage) {
            if (isset($this->data[$target]) || array_key_exists($target, $this->data)) {
                $this->oldData[$target] = $this->data[$target];
                $this->owner[$storage] = $this->encode($this->data[$target]);
            }
        }
    }

    /**
     * @param array $data
     */
    public function setAttributes(array $data)
    {
        $this->attributes = $data;
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function getValue($name)
    {
        if (isset($this->data[$name]) || array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        return null;
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