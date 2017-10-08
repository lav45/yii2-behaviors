<?php

use yii\db\Migration;

class m171008_212507_SerializeBehavior extends Migration
{
    public function up()
    {
        $this->createTable('news', [
            'id' => $this->primaryKey(),
            '_data' => $this->text(),
        ]);
    }
}
