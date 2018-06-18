<?php

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

new \yii\console\Application([
    'id' => 'unit',
    'basePath' => __DIR__,
    'timeZone' => 'UTC',
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'sqlite::memory:',
        ],
        'formatter' => [
            'dateFormat' => 'dd.MM.yyyy',
            'timeFormat' => 'HH:mm',
            'datetimeFormat' => 'dd.MM.yyyy HH:mm',
        ],
    ]
]);

Yii::$app->runAction('migrate/up', [
    'migrationPath' => __DIR__ . '/migrations',
    'interactive' => 0
]);
