# PushModelBehavior

This Behavior proxies the data into a model with your custom logic.
This can be useful if you need to transfer data to another server by events in the owner model.


## Configuration

```php
class User extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                '__class' => PushModelBehavior::class,

                // The model that will receive the data
                'targetClass' => TargetModel::class,

                // What fields should be changed and where to send data
                // @see \lav45\behaviors\traits\WatchAttributesTrait::setAttributes()
                'attributes' => [
                    [
                        'watch' => 'email', // watch for changes in a few fields
                        'field' => 'id', // set value in this relation attribute
                        'value' => function () { // get value from the attribute or path
                            return $this->employee->id;
                        },
                    ],
                    'email',
                ],

                'events' => [
                    // The method that will be called from the target model on the event [[ActiveRecord::EVENT_AFTER_INSERT]]
                    ActiveRecord::EVENT_AFTER_INSERT => 'save',

                    // The callback that will be called from the target model on the event [[ActiveRecord::EVENT_AFTER_UPDATE]]
                    ActiveRecord::EVENT_AFTER_UPDATE => function (TargetModel $model) {
                        $model->save();
                    },

                    // Another option is to assign your handler
                    ActiveRecord::EVENT_AFTER_DELETE => [$this, 'targetAfterDelete'],
                ],
            ],
        ];
    }

    /**
     * @param TargetModel $model
     */ 
    public function targetAfterDelete(TargetModel $model)
    {
        $model->delete();
    }
}

class TargetModel extends \yii\base\Model
{
    public $id;

    public $email;

    public function rules()
    {
        return [
            [['id', 'email'], 'required'],
            [['email'], 'email'],
        ];
    }    

    public function save()
    {
        // If all data has been verified
        if (!$this->validate()) {
            return;
        }
    
        // For example, you can send data through the Rest API
        Yii::$app->httpClient
            ->post(['user', 'id' => $this->id], ['email' => $this->email])
            ->send();
    }

    public function delete()
    {
        if (!$this->validate()) {
            return;
        }

        Yii::$app->httpClient
            ->delete(['user', 'id' => $this->id])
            ->send();
    }
}
```
