<?php

namespace lav45\behaviors\contracts;

interface OldAttributeInterface extends AttributeInterface
{
    public function getOldAttribute($name);
}