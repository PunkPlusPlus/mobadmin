<?php

use yii\db\Migration;

/**
 * Class m211205_102833_create_tbl_namings
 */
class m211205_102833_create_tbl_namings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('tbl_namings', [
            'id' => $this->primaryKey(),
            'app_id' => $this->integer(),
            'link_id' => $this->integer(),
            'archived' => $this->boolean()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('tbl_namings');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m211205_102833_create_tbl_namings cannot be reverted.\n";

        return false;
    }
    */
}
