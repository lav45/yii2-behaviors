<?php

namespace lav45\behaviors\contracts;

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
}