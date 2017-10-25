<?php

namespace lav45\behaviors\traits;

trait SerializeTrait
{
    /**
     * @var \Closure|array|string
     * @see Json::encode()
     */
    public $encode = 'yii\helpers\Json::encode';
    /**
     * @var \Closure|array|string
     * @see Json::decode()
     */
    public $decode = 'yii\helpers\Json::decode';

    /**
     * @param array $value
     * @return string
     */
    protected function encode($value)
    {
        return call_user_func($this->encode, $value);
    }

    /**
     * @param string $value
     * @return mixed
     */
    protected function decode($value)
    {
        return call_user_func($this->decode, $value);
    }
}