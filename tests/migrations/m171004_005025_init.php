<?php

use yii\db\Migration;

class m171004_005025_init extends Migration
{
    public function up()
    {
        // SerializeBehavior
        // SerializeProxyBehavior
        $this->createTable('news', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            '_data' => $this->text(),
            '_tags' => $this->text(),
            '_options' => $this->text(),
        ]);

        // PushBehavior
        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'login' => $this->string()->notNull(),
            'first_name' => $this->string()->notNull(),
            'last_name' => $this->string(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'last_login' => $this->integer(),
            'company_id' => $this->integer(),
        ]);

        // hasOne
        $this->createTable('user_profile', [
            'user_id' => $this->primaryKey(),
            'birthday' => $this->integer(),
        ]);

        // hasMany
        $this->createTable('user_phone', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'type' => $this->string()->notNull(),
            'phone' => $this->string()->notNull(),
        ]);

        // push to many relations
        $this->createTable('company', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
        ]);

        $this->createTable('api_user', [
            'id' => $this->primaryKey(),
            'user_login' => $this->string()->notNull(),
            'fio' => $this->string()->notNull(),
            'createdAt' => $this->integer()->notNull(),
            'updatedAt' => $this->integer()->notNull(),
            'lastLogin' => $this->integer(),
            'birthday' => $this->integer(),
            'phones' => $this->string()->defaultValue('[]'),
            'company_id' => $this->integer(),
            'company_name' => $this->string(),
        ]);

        // PushModelBehavior
        $this->createTable('push_model', [
            'id' => $this->primaryKey(),
            'username' => $this->string(),
        ]);
    }
}