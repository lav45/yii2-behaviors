<?php

namespace lav45\behaviors\tests\models;

use yii\base\Model;

class TargetModel extends Model
{
    const ACTION_AFTER_INSERT = 'insert';
    const ACTION_AFTER_UPDATE = 'update';
    const ACTION_BEFORE_DELETE = 'beforeDelete';
    const ACTION_AFTER_DELETE = 'afterDelete';

    /** @var array */
    public static $lastAction = [];
    /** @var array */
    public static $lastAttributes = [];
    /** @var int */
    public $id;
    /** @var string */
    public $login;

    public function insert()
    {
        static::$lastAction[] = self::ACTION_AFTER_INSERT;
        static::$lastAttributes = $this->attributes;
    }

    public function update()
    {
        static::$lastAction[] = self::ACTION_AFTER_UPDATE;
        static::$lastAttributes = $this->attributes;
    }

    public function beforeDelete()
    {
        static::$lastAction[] = self::ACTION_BEFORE_DELETE;
    }

    public function afterDelete()
    {
        static::$lastAction[] = self::ACTION_AFTER_DELETE;
        static::$lastAttributes = $this->attributes;
    }
}