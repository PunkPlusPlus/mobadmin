<?php

use yii\db\Migration;

/**
 * Class m210916_080346_create_tbl_queue
 */
class m210916_080346_create_tbl_queue extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	$this->createTable('{{%tbl_queue}}', [
           'id' => $this->primaryKey(),
           'app_id' => $this->integer(),
           'count' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%tbl_queue}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210916_080346_create_tbl_queue cannot be reverted.\n";

        return false;
    }
    */
}
