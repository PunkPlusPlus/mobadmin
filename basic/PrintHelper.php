<?php


namespace app\basic;


class PrintHelper
{
    public static function printCount($count, $tag = 'span')
    {
        $color = ($count >= 0) ? 'green' : 'red';
        $sign = ($count >= 0) ? '' : '-';
        $html = "<{$tag} style=\"color:{$color}\">{$sign}$" . abs($count) . "</${tag}>";
        return $html;
    }
}