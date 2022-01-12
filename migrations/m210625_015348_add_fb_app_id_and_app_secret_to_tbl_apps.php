<?php

use yii\db\Migration;

/**
 * Class m210614_002128_add_app_secret_column_to_apps
 */
class m210625_015348_add_fb_app_id_and_app_secret_to_tbl_apps extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tbl_apps', 'fb_app_id', 'text');
        $this->addColumn('tbl_apps', 'app_secret', 'text');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('tbl_apps', 'app_secret');
        $this->dropColumn('tbl_apps', 'fb_app_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210614_002128_add_app_secret_column_to_apps cannot be reverted.\n";

        return false;
    }
    */
}
