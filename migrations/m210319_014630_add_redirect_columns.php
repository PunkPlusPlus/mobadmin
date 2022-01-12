<?php

use yii\db\Migration;
use yii\db\Schema;

/**
 * Class m210319_014630_add_redirect_columns
 */
class m210319_014630_add_redirect_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $table = Yii::$app->db->schema->getTableSchema('tbl_visits');
        if(isset($table->columns['redirect'])) {
            $this->dropColumn('tbl_visits', 'redirect');
        }

        $this->addColumn('tbl_visits', 'is_redirect', Schema::TYPE_TINYINT);
        $this->addColumn('tbl_visits', 'redirect_data', Schema::TYPE_TEXT);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('tbl_visits', 'is_redirect');
        $this->dropColumn('tbl_visits', 'redirect_data');
    }
}
