<?php
use yii\db\Migration;

/**
 * Class m210902_014138_add_uuid_to_tbl_apps
 */
class m210902_014138_add_uuid_to_tbl_apps extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	$this->addColumn('tbl_apps', 'uuid', 'text');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('tbl_apps', 'uuid');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210902_014138_add_uuid_to_tbl_apps cannot be reverted.\n";

        return false;
    }
    */
}
