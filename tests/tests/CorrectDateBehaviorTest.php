<?php

namespace lav45\behaviors\tests\tests;

use lav45\behaviors\CorrectDateBehavior;
use lav45\behaviors\tests\models\CorrectDateModel;
use PHPUnit\Framework\TestCase;
use Yii;

class CorrectDateBehaviorTest extends TestCase
{
    public function testSet()
    {
        $model = new CorrectDateModel();
        $model->dateFrom = '13.03.2018 01:35';
        $this->assertEquals(1520904900, $model->date_from);
    }

    public function testGet()
    {
        $model = new CorrectDateModel();
        $model->date_to = 1520904900;
        $this->assertEquals('13.03.2018 01:35', $model->dateTo);
    }

    public function testSetCustomFormatter()
    {
        $model = new CorrectDateModel();
        /** @var CorrectDateBehavior $behavior */
        $behavior = $model->getBehavior('correctDate');
        $behavior->formatter = [
            'datetimeFormat' => 'php:M d, Y, H:i:s A',
        ];

        $model->date_to = 1520904900;

        $this->assertEquals('Mar 13, 2018, 01:35:00 AM', $model->dateTo);
    }

    public function testSetCustomFormat()
    {
        $model = new CorrectDateModel();
        /** @var CorrectDateBehavior $behavior */
        $behavior = $model->getBehavior('correctDate');
        $behavior->format = 'date';

        $model->date_to = 1520904900;
        $this->assertEquals('13.03.2018', $model->dateTo);
    }

    public function testSetCustomTimeZone()
    {
        Yii::$app->formatter->timeZone = 'Europe/Minsk';

        $model = new CorrectDateModel();
        $model->dateFrom = '13.03.2018 01:35';

        $this->assertEquals(1520894100, $model->date_from);
    }
}
