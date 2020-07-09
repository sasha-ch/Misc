<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage plugins
 */


/**
 * Убирает параметр param из хвоста url-я
 * Если указан val - заменяет значение param-а
 *
 * @param url
 * @param param
 *
 * @return url
 */
function smarty_modifier_href_param_cut($url, $param, $val = '')
{
    $fragments = parse_url($url);
    parse_str($fragments['query'], $params);
    foreach ($params as $k => $v) {
        if ($k == $param) {
            if (empty($val)) {
                unset($params[$k]);
            } else {
                $params[$k] = $val;
            }
            break;
        }
    }
    $fragments['query'] = http_build_query($params);
    $url = http_build_urlx($fragments, null);

    return $url;
}
