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

- [News](/tests/models/News.php)
