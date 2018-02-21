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


## Options


### storageAttribute 

* Type: `string` 
* Require: `true`
* Example: `'data'`

Field in the database in which all data will be stored


### attributes

* Type: `array`
* Require: `true`
* Example: 
```php
'attributes' => [
    // simple text field
    'description', 
    
    // field in the form of an array that will be used by default
    'meta' => [ 
        'keywords' => null,
        'description' => null,
    ],
    
    // default value
    'is_active' => true,

    // callback as default value
    'date' => function() {
        return $this->updated_at ?: $this->created_at;
    }
]
```

A list of virtual fields that will be stored in the `storageAttribute`


### encode

* Type: `\Closure` | `array` | `string`
* Require: `false`
* Default: `yii\helpers\Json::encode`
* Example: 
```php
'encode' => function(array $value) {
    return serialize($value);
},
```
or
```php
'encode' => function(array $value) {
    return new \yii\db\JsonExpression($value, 'jsonb');
},
```

Method that will be used to encode data


### decode

* Type: `\Closure` | `array` | `string`
* Require: `false`
* Default: `yii\helpers\Json::decode`
* Example:
```php
'decode' => function($value) {
    return unserialize($value); 
},
```
or
```php
'decode' => function($value) {
    return $value; // If the database itself returns an array 
},
```

Method that will be used to decode data