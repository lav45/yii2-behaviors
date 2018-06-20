<?php

namespace lav45\behaviors\traits;

/**
 * Class ChangeAttributesTrait
 * @package lav45\behaviors
 */
trait ChangeAttributesTrait
{
    /**
     * @var array
     */
    private $data = [];
    /**
     * @var array
     */
    private $oldData = [];

    /**
     * @param string $name
     * @param bool $identical
     * @return bool
     */
    public function isAttributeChanged($name, $identical = true)
    {
        if (isset($this->data[$name], $this->oldData[$name])) {
            if ($identical) {
                return $this->data[$name] !== $this->oldData[$name];
            }
            return $this->data[$name] != $this->oldData[$name];
        }
        return isset($this->data[$name]) || isset($this->oldData[$name]);
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getOldAttribute($name)
    {
        return isset($this->oldData[$name]) || array_key_exists($name, $this->oldData) ? $this->oldData[$name] : null;
    }
}