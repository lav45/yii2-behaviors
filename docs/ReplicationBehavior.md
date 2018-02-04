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

- [Page](/tests/models/Page.php)
- [PageReplication](/tests/models/PageReplication.php)
