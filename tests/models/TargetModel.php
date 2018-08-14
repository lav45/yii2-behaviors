<?php

namespace lav45\behaviors\tests\models;

use yii\base\Model;

class TargetModel extends Model
{
    const ACTION_INSERT = 'insert';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    /** @var string */
    public static $lastAction;
    /** @var array */
    public static $lastAttributes = [];
    /** @var int */
    public $id;
    /** @var string */
    public $login;

    public function insert()
    {
        static::$lastAction = self::ACTION_INSERT;
        static::$lastAttributes = $this->attributes;
    }

    public function update()
    {
        static::$lastAction = self::ACTION_UPDATE;
        static::$lastAttributes = $this->attributes;
    }

    public function beforeDelete()
    {
        static::$lastAction = self::ACTION_DELETE;
        static::$lastAttributes = $this->attributes;
    }

    public function afterDelete()
    {
        static::$lastAction = self::ACTION_DELETE;
        static::$lastAttributes = $this->attributes;
    }
}