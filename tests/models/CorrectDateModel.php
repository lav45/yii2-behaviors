<?php

namespace lav45\behaviors\tests\models;

use lav45\behaviors\CorrectDateBehavior;
use yii\base\Model;

/**
 * Class CorrectDateModel
 * @package lav45\behaviors\tests\models
 *
 * @property string $dateFrom
 * @property string $dateTo
 */
class CorrectDateModel extends Model
{
    /**
     * @var int
     */
    public $date_from;
    /**
     * @var int
     */
    public $date_to;

    public function behaviors()
    {
        return [
            'correctDate' => [
                'class' => CorrectDateBehavior::class,
                'attributes' => [
                    'dateFrom' => 'date_from',
                    'dateTo' => 'date_to',
                ]
            ]
        ];
    }
}