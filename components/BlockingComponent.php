<?php
namespace app\components;


use app\models\Blocking;
use app\models\BlockType;

class BlockingComponent
{

    /**
     * @var bool
     */
    public $result;

    /**
     * @var array
     */
    public $collectedData;

    /**
     * @var array
     */
    public $debugArray;

    /**
     * @var array
     */
    public $types;


    /**
     * BlockingComponent constructor.
     * Possible variants ['ASN','UserAgent','OS','IPv6','IPv4','Browser',..]
     * @param $collectedData
     */
    public function __construct($collectedData)
    {
        $this->getBaseTypes();
        $this->collectedData = $collectedData;
        $this->parseCollectedData();
    }


    public function block($return = false)
    {
        $result = false;

        $blockingData = Blocking::find()
            ->where(['active'=>'yes'])
            ->andWhere(['deleted'=>'no'])
            ->orderBy(['block_position'=>'ASC'])
            ->all();

        if ($blockingData) {
            $compare_result = [];
            foreach ($blockingData as $item){
                //$this->debugArray['blocking_filter_data'][$item->id] = $item->toArray();

                foreach ($this->types as $id=>$type) {

                    if($item->block_type == $id) {

                        $compare_result[$item->id] = (!empty($this->collectedData[$type])) &&
                            (new CompareComponent($item->blockMethod['defined_key'],$item->block_value,$this->collectedData[$type]))
                                ->compare(true);

                        if($compare_result[$item->id]) {
                            $this->debugArray['blocking_blocked_by'][$type][$item->id]['pattern'] = $item->block_value;
                            $this->debugArray['blocking_blocked_by'][$type][$item->id]['value'] = $this->collectedData[$type];
                            $this->debugArray['blocking_blocked_by'][$type][$item->id]['method'] = $item->block_method;
                        }

                    }

                } // foreach types

            }


            //$this->debugArray['blocking_filter_result_array'] = $compare_result;
            foreach ($compare_result as $_item){
                if($_item) {
                    $result = true;
                }
            }

        } else {
            $this->debugArray['blocking_error'][] = 'Cannot retrieve data from database';
            $result = false;
        }

        $this->debugArray['blocking_filter_result_bool'] = $result;
        $this->result = $result;
        if($return) return $this->result;
    }


    private function parseCollectedData()
    {
        foreach ($this->types as $p) {
            if(isset($this->collectedData[$p])) {
                //$this->$p = $this->collectedData[$p];
                $this->debugArray['blocking_data_array'][$p] = $this->collectedData[$p];
            }
        }
    }

    private function getBaseTypes()
    {
        $classTypes = [];
        $types = BlockType::find()->where(['active'=>'yes'])->all();

        if($types) {
            foreach ($types as $type) {
                $classTypes[$type->id] = $type->varname;
            }

            $this->types = $classTypes;
        }

    }


}