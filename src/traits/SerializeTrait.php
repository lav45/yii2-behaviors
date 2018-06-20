<?php

namespace lav45\behaviors\traits;

trait SerializeTrait
{
    /**
     * @var \Closure|array|string|bool method that will be used to encode data
     * If you set the value to false, no action will be taken
     */
    public $encode;
    /**
     * @var \Closure|array|string|bool method that will be used to decode data
     * If you set the value to false, no action will be taken
     */
    public $decode;

    /**
     * @param array $value
     * @return string|array
     */
    protected function encode($value)
    {
        if (false === $this->encode) {
            return $value;
        }
        if (null === $this->encode) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        return call_user_func($this->encode, $value);
    }

    /**
     * @param string $value
     * @return mixed
     */
    protected function decode($value)
    {
        if (false === $this->decode) {
            return $value;
        }
        if (null === $this->decode) {
            return json_decode($value, true);
        }
        return call_user_func($this->decode, $value);
    }
}