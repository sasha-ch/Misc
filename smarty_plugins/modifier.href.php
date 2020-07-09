<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage plugins
 */

function smarty_modifier_href($string)
{
    return preg_replace("/(\s)(http:\/\/\S+)(\s)/i", "$1<a href='$2' target=_blank>$2</a>$3", $string);
}
