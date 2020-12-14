<?php

namespace lav45\behaviors;

use Closure;
use yii\db\ActiveRecord;
use lav45\behaviors\contracts\AttributeChangeInterface;
use lav45\behaviors\contracts\OldAttributeInterface;
use lav45\behaviors\traits\ChangeAttributesTrait;
use lav45\behaviors\traits\SerializeTrait;

/**
 * Class SerializeBehavior
 * @package lav45\behaviors
 *
 * @property-write array $attributes
 */
class SerializeBehavior extends AttributeBehavior implements AttributeChangeInterface, OldAttributeInterface
{
    use SerializeTrait;

    use ChangeAttributesTrait;

    /**
     * @var string field in the database in which all data will be stored
     */
    public $storageAttribute;
    /**
     * @var bool
     */
    private $changeStorageAttribute = false;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'loadData',
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
        ];
    }

    public function loadData()
    {
        $data = $this->decode($this->owner[$this->storageAttribute]);
        $this->oldData = $this->data = $data ?: [];
    }

    public function beforeSave()
    {
        if ($this->data !== $this->oldData) {
            $this->owner[$this->storageAttribute] = $this->encode($this->data);
            $this->changeStorageAttribute = true;
        }
    }

    public function afterSave()
    {
        $this->oldData = $this->data;
        $this->changeStorageAttribute = false;
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

        if ($value instanceof Closure) {
            return $value();
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
        if ($this->changeStorageAttribute === true) {
            $this->beforeSave();
        }
    }
}
