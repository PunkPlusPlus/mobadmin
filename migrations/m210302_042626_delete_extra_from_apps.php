<?php

use yii\db\Migration;

/**
 * Class m210302_042626_delete_extra_from_apps
 */
class m210302_042626_delete_extra_from_apps extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('tbl_apps', 'extra');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210302_042626_delete_extra_from_apps cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210302_042626_delete_extra_from_apps cannot be reverted.\n";

        return false;
    }
    */
}
