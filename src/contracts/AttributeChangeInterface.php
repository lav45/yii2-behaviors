<?php

namespace lav45\behaviors\contracts;

interface AttributeChangeInterface
{
    /**
     * @param string $name
     * @param bool $identical
     * @return bool
     */
    public function isAttributeChanged($name, $identical = true);
}