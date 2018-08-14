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
            '__class' => PushModelBehavior::class,
            'targetClass' => TargetModel::class,
            'triggerAfterInsert' => 'save',
            'triggerAfterUpdate' => function (TargetModel $model) {
                $model->save();
            },
            'triggerBeforeDelete' => null,
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
    $model->delete();
}
```

## Description

When CRUD data on owner model and exist id data will push on TargetModel and will cause described action in triggers.


## Example

- [TargetModel](/tests/models/TargetModel.php)
