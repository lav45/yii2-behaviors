# PushModelBehavior

This extension will help you to transfer data to other models when saving, updating and deleting owner model.

## Configuration

```
/**
 * @inheritdoc
 */
public function behaviors()
{
    return [
        [
                'class' => PushModelBehavior::class,
                'targetClass' => TargetModel::class,
                'triggerInsert' => function (TargetModel $model) {
                    if ($this->employee->id) {
                        $model->save();
                    }
                },
                'triggerUpdate' => function (TargetModel $model) {
                    if ($this->employee->id) {
                        $model->save();
                    }
                },
                'triggerBeforeDelete' => false,
                'triggerAfterDelete' => [$this, 'targetAfterDelete'],
                'attributes' => [
                    [
                        'watch' => 'email',
                        'field' => 'id',
                        'value' => function () {
                            return $this->employee->id;
                        },
                    ],
                    'email',
                ],
        ],
    ];
}

/**
 * @param TargetModel $model
 */ 
public function targetAfterDelete(TargetModel $model)
{
    if ($this->employee->id) {
        $model->delete();
    }
}
```

## Description

When CRUD data on owner model and exist id data will push on TargetModel and will cause described action in triggers.


## Example

- [TargetModel](/tests/models/TargetModel.php)
