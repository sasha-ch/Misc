<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage plugins
 */

function smarty_modifier_tab($string)
{
    return str_replace("\t", "<span class='tab'>&nbsp;</span>", $string);
}

?>
