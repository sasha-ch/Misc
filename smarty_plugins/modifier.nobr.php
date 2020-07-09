<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage plugins
 */


/**
 * Smarty plugin
 */
function smarty_modifier_nobr($string)
{
    return str_replace(' ', "&nbsp;", $string);
}
