<?php

namespace lav45\behaviors\tests\units;

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
        self::assertEquals(1520904900, $model->date_from);
    }

    public function testGet()
    {
        $model = new CorrectDateModel();
        $model->date_to = 1520904900;
        self::assertEquals('13.03.2018 01:35', $model->dateTo);
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

        self::assertEquals('Mar 13, 2018, 01:35:00 AM', $model->dateTo);
    }

    public function testSetCustomFormat()
    {
        $model = new CorrectDateModel();
        /** @var CorrectDateBehavior $behavior */
        $behavior = $model->getBehavior('correctDate');
        $behavior->format = 'date';

        $model->date_to = 1520904900;
        self::assertEquals('13.03.2018', $model->dateTo);
    }

    public function testSetCustomTimeZone()
    {
        Yii::$app->formatter->timeZone = 'Europe/Minsk';

        $model = new CorrectDateModel();
        $model->dateFrom = '13.03.2018 01:35';

        self::assertEquals(1520894100, $model->date_from);
    }

    public function testDateUTCTimeZone()
    {
        Yii::$app->formatter->timeZone = 'Europe/Minsk';

        $model = new CorrectDateModel();
        /** @var CorrectDateBehavior $correctDate */
        $correctDate = $model->getBehavior('correctDate');
        $correctDate->format = 'date';
        $correctDate->formatter = [
            'timeZone' => 'UTC',
            'datetimeFormat' => 'dd.MM.yyyy HH:mm:ss',
        ];

        $model->dateFrom = '13.03.2018';

        self::assertEquals(strtotime('13.03.2018 UTC'), $model->date_from);
    }

    public function testIncorrectDate()
    {
        $model = new CorrectDateModel();
        $model->dateFrom = '13.03.____';
        self::assertNull($model->date_from);
    }

    public function testIncorrectDateException()
    {
        $model = new CorrectDateModel();

        /** @var CorrectDateBehavior $correctDate */
        $correctDate = $model->getBehavior('correctDate');
        $correctDate->throwException = true;

        $this->expectException('yii\base\InvalidArgumentException');

        if (PHP_VERSION_ID >= 80100) {
            $this->expectExceptionMessageRegExp('/is not a valid date time value: Failed to parse time string/i');
        } else {
            $this->expectExceptionMessageRegExp('/is not a valid date time value: DateTime::__construct\(\): Failed to parse time string/i');
        }

        $model->dateFrom = '13.03.____';
    }
}
