<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage plugins
 */

function smarty_modifier_checked($val)
{
    if ($val == '1') {
        return 'checked';
    } else {
        return '';
    }
}
