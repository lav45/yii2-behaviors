<?php

namespace lav45\behaviors\contracts;

/**
 * Interface AttributeChangeInterface
 * @package lav45\behaviors\contracts
 */
interface AttributeChangeInterface extends AttributeInterface
{
    /**
     * @param string $name
     * @param bool $identical
     * @return bool
     */
    public function isAttributeChanged($name, $identical = true);

    /**
     * @param string $name
     * @return mixed
     */
    public function getOldAttribute($name);

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setOldAttribute($name, $value);
}