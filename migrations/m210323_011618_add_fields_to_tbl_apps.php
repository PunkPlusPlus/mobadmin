<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m210323_011618_add_fields_to_tbl_apps
 */
class m210323_011618_add_fields_to_tbl_apps extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tbl_apps', 'keystore', Schema::TYPE_TEXT);
        $this->addColumn('tbl_apps', 'github', Schema::TYPE_TEXT);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('tbl_apps', 'keystore');
        $this->dropColumn('tbl_apps', 'github');
    }
}
