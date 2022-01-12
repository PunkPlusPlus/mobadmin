<?php

use yii\db\Migration;

/**
 * Class m210902_053339_add_ip_to_blacklist
 */
class m210902_053339_add_ip_to_blacklist extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	$this->addColumn('tbl_black_list', 'ip', 'text');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
	$this->dropColumn('tbl_black_list', 'ip', 'text');        
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210902_053339_add_ip_to_blacklist cannot be reverted.\n";

        return false;
    }
    */
}
