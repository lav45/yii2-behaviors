<?php

namespace lav45\behaviors\contracts;

interface AttributeChangeInterface extends AttributeInterface
{
    public function isAttributeChanged($name, $identical = true);
}