<?php

/**
 * Detect 'utf-8', 'cp1251', 'koi8-r'
 * Search KOI chars
 * KOI: а-я: 192-223, А-Я: 224-255, pseudographics: 128-191
 *
 * @param string $str
 * @param string $default_encoding
 *
 * @return string
 */
function detect_text_encoding($str, $default_encoding = 'utf-8')
{
    $arr = str_split($str);
    $lower = 0;
    $upper = 0;
    $all = 0;
    foreach ($arr as $char) {
        $num = ord($char);
        if ($num >= 192 && $num <= 223) {
            $lower++;
        }
        if ($num >= 224 && $num <= 255) {
            $upper++;
        }
        if ($num >= 128) {
            $all++;
        }
    }
    $pseudo = $all - $upper - $lower;
    if ($pseudo * 3 > $all) {
        return 'utf-8';
    } elseif ($all < ($upper + $lower) * 1.5) {
        if ($upper / $lower > 10) {
            return 'cp1251';
        } elseif ($lower / $upper > 10) {
            return 'koi8-r';
        }

    }
    //Undetected encoding
    return $default_encoding;
}

/* String float to php/mysql float format 
 * with dot delimiter */
function str2float($s)
{
    setlocale(LC_NUMERIC, "C");
    return floatval(str_replace(',', '.', $s));
}

function clean_html_text($text)
{
    $text = preg_replace('/<(.*)>/iU', '', $text);
    $text = html_entity_decode($text);
    $text = trim($text);
    $text = trim($text, chr(160) . chr(32));
    return $text;
}

if (!function_exists('mb_ucfirst') && function_exists('mb_substr')) {
    function mb_ucfirst($string)
    {
        $string = mb_ereg_replace("^[\ ]+", "", $string);
        $string = mb_strtoupper(mb_substr($string, 0, 1, "UTF-8"), "UTF-8") . mb_substr($string, 1, mb_strlen($string),
                "UTF-8");
        return $string;
    }
}

/**
 * Move english to russian letters in non-english words in UTF-8
 * remove non-alphabetic chars
 */
function clean_ru_str($str)
{
    $table = [
        'e' => 'е',
        'a' => 'а',
        'p' => 'р',
        'o' => 'о',
        'c' => 'с',
        'x' => 'х',
        'y' => 'у',
        'k' => 'к',
        'b' => 'в',
        'n' => 'п',
        'm' => 'м',
    ];
    $words = explode(' ', str_replace("\n", ' ', $str));
    foreach ($words as $i => $w) {
        if (!detect_en_lang($w)) {
            $w = str_replace(array_keys($table), array_values($table), $w);
        }

        $w = mb_ereg_replace("[^A-z0-9а-яё_ ]", "", $w);
        $words[$i] = $w;
    }
    $words = array_unique($words);
    return implode(' ', $words);
}

//If string language is english or some other
function detect_en_lang($str)
{
    return preg_match('/^[A-z0-9_ ]+$/iu', $str) ? true : false;
}

function mb_str_split($str, $length = 1)
{
    $result = preg_match_all("/.{{$length}}/u", $str, $matches);
    if ($result) {
        $result = $matches[0];
        if ($length > 1) {
            $stub = mb_strlen($str) % $length;
            if ($stub) {
                $result[] = mb_substr($str, -$stub, $stub);
            }
        }
    }
    return $result;
}

function translit($str)
{
    $transtable = [
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'д' => 'd',
        'е' => 'e',
        'ё' => 'yo',
        'ж' => 'zh',
        'з' => 'z',
        'и' => 'i',
        'й' => 'y',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'у' => 'u',
        'ф' => 'f',
        'х' => 'h',
        'ц' => 'ts',
        'ч' => 'ch',
        'ш' => 'sh',
        'щ' => 'shch',
        'ъ' => '',
        'ы' => 'y',
        'ь' => '',
        'э' => 'e',
        'ю' => 'yu',
        'я' => 'ya',
        ' ' => '_',
        '-' => '_',
    ];
    $str = mb_strtolower($str, 'UTF-8');
    $str = strtr($str, $transtable);
    return $str;
}
