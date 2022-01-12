<?php

use yii\db\Migration;

/**
 * Class m211109_040142_create_tbl_tokens
 */
class m211109_040142_create_tbl_tokens extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	$this->createTable('{{%tbl_tokens}}', [
            'id' => $this->primaryKey(),
            'token' => $this->string(255),
            'user_id' => $this->integer(255)
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_tokens}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211109_040142_create_tbl_tokens cannot be reverted.\n";

        return false;
    }
    */
}
