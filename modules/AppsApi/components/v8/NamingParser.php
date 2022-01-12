<?php

namespace app\modules\AppsApi\components\v8;

class NamingParser
{
    private $naming;
    private $separator;
    private $subList;

    public function __construct($naming)
    {
        $this->naming = $naming;
        $this->findSeparator();
        $this->parseSubList();
    }

    private function findSeparator()
    {
        if ($this->naming != null) {
            $separatorList = ["~", "/", "|", ";", "_"];
            for ($i = 0; $i < count($separatorList); $i++) {
                if (!isset($separator)) {
                    $isSeparator = strpos($this->naming, $separatorList[$i]);
                    //debugHelper::print($i." - ".$isSeparator, false);
                    if ($isSeparator) {
                        $separator = $separatorList[$i];
                    }
                }
            }
            $this->separator = $separator ?? false;
        }
    }

    private function parseSubList()
    {
        $this->subList = explode($this->separator, $this->naming);
    }

    public function getSubList()
    {
        return $this->subList ?? null;
    }

}