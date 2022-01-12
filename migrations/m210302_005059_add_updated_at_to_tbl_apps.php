<?php

use yii\db\Migration;

/**
 * Class m210302_005059_add_updated_at_to_tbl_apps
 */
class m210302_005059_add_updated_at_to_tbl_apps extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tbl_apps', 'updated_at', 'int(11)');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('tbl_apps', 'updated_at');
    }
}
