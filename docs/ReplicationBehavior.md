# ReplicationBehavior

This extension will help you to copy data from one table to the remote and to maintain the relevance of the data, tracking changes in field list `attributes`.


## Using

```php
PageReplication::find()->count(); // => 0

$model = new Page();
$model->text = 'some text';
$model->save();

$replicationModel = PageReplication::findOne($model->id);
echo $replicationModel->description; // some text
```


## Configuration

```php
class Page extends ActiveRecord
{
    /**
     * It would be nice to use transaction
     */
    public function transactions()
    {
        return [
            ActiveRecord::SCENARIO_DEFAULT => ActiveRecord::OP_ALL,
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => 'lav45\behaviors\ReplicationBehavior',
                'relation' => 'pageReplication',
                'attributes' => [
                    'id' => 'id',
                    'text' => 'description',
                    'created_at' => [
                        'field' => 'createdAt',
                        'value' => function () {
                            return $this->created_at;
                        }
                    ],
                    'updated_at' => [
                        'field' => 'updatedAt',
                        'value' => 'data.updated'
                    ]
                ]
            ]
        ];
    }

    public function getPageReplication()
    {
        return $this->hasOne(PageReplication::className(), ['id' => 'id']);
    }

    public function getData()
    {
        return [
            'id' => $this->id,
            'updated' => $this->updated_at,
            'created' => $this->created_at,
        ];
    }
}
```