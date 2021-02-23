<?php

namespace lav45\behaviors\contracts;

/**
 * Interface AttributeInterface
 * @package lav45\behaviors\contracts
 */
interface AttributeInterface
{
    /**
     * @param string $name
     * @return bool
     */
    public function hasAttribute($name);

    /**
     * @param string $name
     * @return mixed
     */
    public function getAttribute($name);

    /**
     * @param string $name
     * @param mixed $value
     */
    public function setAttribute($name, $value);
}