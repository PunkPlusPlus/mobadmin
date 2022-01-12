<?php

use yii\db\Migration;

/**
 * Class m210302_042122_add_apk_column_to_apps
 */
class m210302_042122_add_apk_column_to_apps extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tbl_apps', 'apk', 'text');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('tbl_apps', 'apk', 'text');
    }

}
