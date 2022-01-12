<?php

use yii\db\Migration;

/**
 * Class m210513_061307_add_campaign_af_in_visit
 */
class m210513_061307_add_campaign_af_in_visit extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('tbl_visits', 'campaign_af', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('tbl_visits', 'campaign_af');
    }
}
