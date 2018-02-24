<?php

namespace lav45\behaviors\traits;

trait SerializeTrait
{
    /**
     * @var \Closure|array|string|bool method that will be used to encode data
     * If you set the value to false, no action will be taken
     * @see Json::encode()
     */
    public $encode = 'yii\helpers\Json::htmlEncode';
    /**
     * @var \Closure|array|string|bool method that will be used to decode data
     * If you set the value to false, no action will be taken
     * @see Json::decode()
     */
    public $decode = 'yii\helpers\Json::decode';

    /**
     * @param array $value
     * @return string|array
     */
    protected function encode($value)
    {
        if ($this->encode === false) {
            return $value;
        }
        return call_user_func($this->encode, $value);
    }

    /**
     * @param string $value
     * @return mixed
     */
    protected function decode($value)
    {
        if ($this->decode === false) {
            return $value;
        }
        return call_user_func($this->decode, $value);
    }
}