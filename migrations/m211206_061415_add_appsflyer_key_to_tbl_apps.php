<?php

use yii\db\Migration;

/**
 * Class m211206_061415_add_appsflyer_key_to_tbl_apps
 */
class m211206_061415_add_appsflyer_key_to_tbl_apps extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	$this->addColumn('tbl_apps', 'appsflyer', 'text');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('tbl_apps', 'appsflyer');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211206_061415_add_appsflyer_key_to_tbl_apps cannot be reverted.\n";

        return false;
    }
    */
}
