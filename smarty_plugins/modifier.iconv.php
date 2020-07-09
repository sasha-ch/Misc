<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage plugins
 */


function smarty_modifier_iconv($string, $in_ch = "windows-1251", $out_ch = "UTF-8")
{
    $cur_encoding = mb_detect_encoding($string);
    if ($cur_encoding == $out_ch && mb_check_encoding($string, $out_ch)) {
        return $string;
    } else {
        return iconv($in_ch, $out_ch, $string);
    }
}