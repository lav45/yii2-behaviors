# SerializeBehavior

This extension will help you to create a virtual field for your ActiveRecord models which will store the serialized data in one of the fields of the model.


## Using

```php
$model = new News();
$model->save();

print_r($model->_data); // null
print_r($model->title); // null
print_r($model->meta['keywords']); // null

print_r($model->is_active); // true
$model->is_active = 1;
print_r($model->getOldAttribute('is_active')); // null

$model->save();
print_r($model->_data); // {"is_active": 1}
```


## Configuration

- [News](/tests/models/News.php)
