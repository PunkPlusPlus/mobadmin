<?php

use yii\db\Migration;

/**
 * Class m210913_001400_add_onesignal_id_to_tbl_visits
 */
class m210913_001400_add_onesignal_id_to_tbl_visits extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	$this->addColumn('tbl_visits', 'onesignal_id', 'text');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('tbl_visits', 'onesignal_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210913_001400_add_onesignal_id_to_tbl_visits cannot be reverted.\n";

        return false;
    }
    */
}
