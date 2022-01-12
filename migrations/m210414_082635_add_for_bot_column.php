<?php

use yii\db\Migration;

/**
 * Class m210414_082635_add_for_bot_column
 */
class m210414_082635_add_for_bot_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tbl_params', 'is_for_bot', $this->boolean()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('tbl_params', 'is_for_bot');
    }
}
