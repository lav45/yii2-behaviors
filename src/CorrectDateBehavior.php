<?php

namespace lav45\behaviors;

use Exception;
use yii\di\Instance;
use yii\i18n\Formatter;

/**
 * Class CorrectDateBehavior
 * @package lav45
 *
 *  public function behaviors()
 *  {
 *      return [
 *          [
 *              'class' => CorrectDateBehavior::class,
 *              'formatter' => [
 *                  'timeZone' => 'UTC',
 *                  'datetimeFormat' => 'dd.MM.yyyy HH:mm:ss',
 *              ],
 *              'format' => 'datetime',
 *              'attributes' => [
 *                  'dateFrom' => 'date_from',
 *                  'dateTo' => 'date_to',
 *              ]
 *          ]
 *      ];
 *  }
 */
class CorrectDateBehavior extends AttributeBehavior
{
    /** @var array flip target attributes */
    public $attributes;
    /** @var string|array|Formatter */
    public $formatter = 'formatter';
    /** @var string */
    public $format = 'datetime';
    /** @var bool */
    public $throwException = false;

    /**
     * @return Formatter
     */
    private function getFormatter()
    {
        if (!$this->formatter instanceof Formatter) {
            $this->formatter = Instance::ensure($this->formatter, Formatter::class);
        }
        return $this->formatter;
    }

    /**
     * @param $name string
     * @return string|null
     */
    public function getAttribute($name)
    {
        if ($value = $this->owner->{$this->attributes[$name]}) {
            return $this->getFormatter()->format($value, $this->format);
        }
        return null;
    }

    /**
     * @param string|null $name
     * @param string $value
     */
    public function setAttribute($name, $value)
    {
        $this->owner->{$this->attributes[$name]} = $this->format($value);
    }

    /**
     * @param string $value
     * @return int|null
     * @throws Exception
     */
    protected function format($value)
    {
        if (empty($value)) {
            return null;
        }
        try {
            $formatter = $this->getFormatter();
            return $formatter->asTimestamp($value . ' ' . $formatter->timeZone);
        } catch (Exception $e) {
            if ($this->throwException) {
                throw $e;
            }
            return null;
        }
    }
}