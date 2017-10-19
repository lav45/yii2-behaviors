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

```php
/**
 * @property string $_data
 *
 * // Virtual attributes
 * ---------------------------
 * @property string $title
 * @property array $meta
 * @property bool $is_active
 */
class News extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'serialize' => [
                'class' => 'lav45\behaviors\SerializeBehavior',
                'storageAttribute' => '_data', // Data storage attribute 
                'attributes' => [
                    'title',
                    'meta' => [
                        'keywords' => null,
                        'description' => null,
                    ],
                    'is_active' => true,
                ]
            ]
        ];
    }
    
    /**
     * @return \lav45\behaviors\SerializeBehavior
     */
    protected function getSerializeBehavior()
    {
        return $this->getBehavior('serialize');
    }

    public function isAttributeChanged($name, $identical = true)
    {
        $serialize = $this->getSerializeBehavior();
        if ($serialize->isAttribute($name)) {
            return $serialize->isAttributeChanged($name, $identical);
        }
        return parent::isAttributeChanged($name, $identical);
    }

    public function getOldAttribute($name)
    {
        $serialize = $this->getSerializeBehavior();
        if ($serialize->isAttribute($name)) {
            return $serialize->getOldAttribute($name);
        }
        return parent::getOldAttribute($name);
    }
}
```
