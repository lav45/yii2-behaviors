# CorrectDateBehavior

This extension will help you to convert dates and unixtime to text format and Vice versa


## Using

```php
$model = new News();

$model->date_from = 1524775512;
echo $model->dateFrom; // 26.04.2018 20:45:12

$model->dateFrom = '01.05.2018';
echo $model->date_from; // 1515110400
```


## Configuration

```php
use lav45\behaviors\CorrectDateBehavior;

class News extends Model
{
    public function behaviors()
    {
        return [
            [
                'class' => CorrectDateBehavior::class,
                // 'format' => 'datetime',
                'attributes' => [
                    'dateFrom' => 'date_from',
                    'dateTo' => 'date_to',
                ]
            ]
        ];
    }
}
```
