<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage plugins
 */

/**
 * Замена BB Code на HTML
 *
 * @param string $string
 * @param array  $smiles
 *
 * @return mixed
 *
 */
function smarty_modifier_bb2html($string, $smiles = [])
{

    //шаблоны замены
    $preg = [
        // Text arrtibutes
        '~\[s\](.*?)\[\/s\]~si' => '<del>$1</del>',
        '~\[b\](.*?)\[\/b\]~si' => '<strong>$1</strong>',
        '~\[i\](.*?)\[\/i\]~si' => '<em>$1</em>',
        '~\[u\](.*?)\[\/u\]~si' => '<span stype="text-decoration:underline;">$1</span>',
        '~\[color=(.*?)\](.*?)\[\/color\]~si' => '<span style="color:$1">$2</span>',

        // links
        '~\[url="(.*?)"\](.*?)\[\/url\]~si' => '<a href="$1" target="_blank">$2</a>',

        // images
        '~\[img\](.*?)\[\/img\]~si' => '<img src="$1" alt="$1"/>',

        // quoting
        '~\[q="(.*?)"\](.*?)\[\/q\]~si' => '<span class="quote" id="$1">$2</span>',

        // brief
        '~\[DATA=(.*?)::(.*?)\](.*?)\[\/DATA\]~si' => '<span><b>$1 $2</b></span><br>$3<hr>',
    ];

    // добавляем к шаблонам смайлы из массива
    if ($smiles) {
        foreach ($smiles as $abbr => $img_url) {
            $preg["~" . preg_quote($abbr) . "~"] = "<img src=\"$img_url\" title=\"$abbr\">";
        }
    }

    return preg_replace(array_keys($preg), array_values($preg), $string);
}
