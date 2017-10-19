<?php

namespace lav45\behaviors\contracts;

interface AttributeInterface
{
    public function hasAttribute($name);

    public function getAttribute($name);
}