<?php

/**
 * Test, debug, temporary functions
 */

/**
 * print_r alias. Allow multiple args of any type
 * TODO: $return var
 *
 * @param $v var to print_r
 */
function pr($v)
{
    $args = func_get_args();
    $msg = '';
    foreach ($args as $arg) {
        if (empty($arg)) {
            ob_start();
            var_dump($arg);
            $str = ob_get_clean();
            $msg .= $str . "\n";
        } else {
            $msg .= print_r($arg, true) . "\n";
        }
    }
    if (php_sapi_name() !== 'cli') {
        $msg = '<pre>' . "\n" . $msg . '</pre>' . "\n";
    }

    echo $msg . "\n";
}

function dpr($v)
{
    $args = func_get_args();
    call_user_func_array('pr', $args);
    die;
}

function vd($v)
{
    var_dump($v);
}

function dvd($v)
{
    var_dump($v);
    die;
}


function we($v)
{
    return var_export($v, true);
}

/**
 * Print 2D-array as table
 *
 * @param $in
 * @param $ha
 *
 * @return string
 */
function tpr($in, $ha)
{
    if (empty($ha)) {
        $ha = array_keys(current($in));
    }
    foreach ($ha as $v) {
        $h .= "<th>$v</th>";
    }
    $ss = [];
    foreach ($in as $k => $l) {
        foreach ($l as $v) {
            if (empty($v)) {
                $v = '&nbsp;';
            }
            $ss[$k] .= "<td>$v</td>";
        }
    }
    $s = implode('</tr><tr>', $ss);
    return "<table border=1>
                <thead><tr>$h</tr></thead>
                <tbody><tr>$s</tr></tbody>
            </table>";
}


/**
 * pretty formatted xml string output
 *
 * @param $xml
 */
function pr_xml($xml)
{
    $doc = new DomDocument('1.0');
    $doc->preserveWhiteSpace = false;
    $doc->formatOutput = true;
    $doc->loadXML($xml);
    echo $doc->saveXml();
}

function dpr_class($object)
{
    dpr(get_class($object));
}

function dbt()
{
    debug_print_backtrace(0);
    die;
}

function included_files()
{
    pr(get_included_files());
}
