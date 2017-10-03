<?php

use yii\db\Migration;

class m171004_005025_replicationBehavior extends Migration
{
    public function up()
    {
        $this->createTable('page', [
            'id' => $this->primaryKey(),
            'text' => $this->string(500)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createTable('page_replication', [
            'id' => $this->primaryKey(),
            'description' => $this->string(500)->notNull(),
            'createdAt' => $this->integer()->notNull(),
            'updatedAt' => $this->integer()->notNull(),
        ]);
    }
}