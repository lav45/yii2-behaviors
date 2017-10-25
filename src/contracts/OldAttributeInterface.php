<?php

namespace lav45\behaviors\contracts;

interface OldAttributeInterface
{
    /**
     * @param string $name
     * @return mixed
     */
    public function getOldAttribute($name);
}