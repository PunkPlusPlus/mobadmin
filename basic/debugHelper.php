<?php
namespace app\basic;

class debugHelper{


    public static function print($object, $exit = true){
        print "<pre>";
        print_r($object);
        if($exit)
            exit();
        else
            print "<hr>";
    }

}