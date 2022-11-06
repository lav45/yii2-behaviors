<?php

namespace lav45\behaviors;

use Closure;
use lav45\behaviors\contracts\AttributeChangeInterface;
use lav45\behaviors\traits\ChangeAttributesTrait;
use lav45\behaviors\traits\SerializeTrait;
use yii\db\AfterSaveEvent;
use yii\db\BaseActiveRecord;

/**
 * Class SerializeBehavior
 * @package lav45\behaviors
 *
 * @property-write array $attributes
 */
class SerializeBehavior extends AttributeBehavior implements AttributeChangeInterface
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
            BaseActiveRecord::EVENT_AFTER_FIND => 'loadData',
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
        ];
    }

    /**
     * @param \yii\base\Component $owner
     */
    public function attach($owner)
    {
        parent::attach($owner);
        $owner->on(BaseActiveRecord::EVENT_AFTER_INSERT, [$this, 'afterSave'], null, false);
        $owner->on(BaseActiveRecord::EVENT_AFTER_UPDATE, [$this, 'afterSave'], null, false);
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

    /**
     * @param AfterSaveEvent $event
     */
    public function afterSave(AfterSaveEvent $event)
    {
        $diff = $this->array_diff_recursive($this->data, $this->oldData);
        foreach (array_keys($diff) as $key) {
            $event->changedAttributes[$key] = isset($this->oldData[$key]) ? $this->oldData[$key] : null;
        }

        $this->oldData = $this->data;
        $this->changeStorageAttribute = false;
    }

    /**
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
    protected function array_diff_recursive($arr1, $arr2)
    {
        $result = [];
        foreach ($arr1 as $key => $value) {
            if (isset($arr2[$key]) || array_key_exists($key, $arr2)) {
                if (is_array($value)) {
                    $recursiveDiff = $this->array_diff_recursive($value, $arr2[$key]);
                    if (count($recursiveDiff)) {
                        $result[$key] = $recursiveDiff;
                    }
                } elseif (in_array($value, $arr2, true) === false) {
                    $result[$key] = $value;
                }
            } elseif (in_array($value, $arr2, true) === false) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * @param array $data
     */
    public function setAttributes(array $data)
    {
        $this->attributes = [];
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

        if ($value instanceof Closure || (is_array($value) && is_callable($value))) {
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
