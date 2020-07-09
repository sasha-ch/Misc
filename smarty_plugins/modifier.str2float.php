<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage plugins
 */

function smarty_modifier_str2float($s)
{
	return str_replace(',', '.', $s);
}
