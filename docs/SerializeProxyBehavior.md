# SerializeProxyBehavior

This extension proxy data from ArciveRecord model and back to the database. And converts the obtained data in the required storage format.


## Using

```php
$model = new News();
$model->tags = ['tag1', 'tag2', 'tag3', 'tag4'];
$model->options = [
    'key1' => 'value1',
    'key2' => 'value2',
    'key3' => 'value3',
];

$model->save();

echo $model->_tags; // ["tag1","tag2","tag3","tag4"]
echo $model->_options; // {"key1":"value1","key2":"value2","key3":"value3"}
```


## Configuration

```php
use yii\db\ActiveRecord;
use lav45\behaviors\VirtualAttributesTrait;

/**
 * @property string $_tags
 * @property string $_options
 *
 * // Virtual attributes
 * ---------------------------
 * @property array $tags
 * @property array $options
 */
class News extends ActiveRecord
{
    /**
     * This trait provides support for virtual methods `getAttribute()`, `getOldAttribute()`, `isAttributeChanged()`
     * that implement Behaviors
     */
    use VirtualAttributesTrait;

    public function behaviors()
    {
        return [
            'serializeProxy' => [
                'class' => 'lav45\behaviors\SerializeProxyBehavior',
                'attributes' => [
                    'tags' => '_tags',
                    'options' => '_options',
                ]
            ]
        ];
    }
}
```
