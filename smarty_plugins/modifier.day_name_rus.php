<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage plugins
 */

function smarty_modifier_day_name_rus($val)
{
    $days = [
        'Mon' => 'Пн',
        'Tue' => 'Вт',
        'Wed' => 'Ср',
        'Thu' => 'Чт',
        'Fri' => 'Пт',
        'Sat' => 'Сб',
        'Sun' => 'Вс',
    ];
    return $days[$val];
}
