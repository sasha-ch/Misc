<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage plugins
 */

function smarty_modifier_price($string)
{
	return sprintf("%.2f", str_replace(',', '.', $string));
}
