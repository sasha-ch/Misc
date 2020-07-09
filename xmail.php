<?php

/**
 * Send mail with attachment(s)
 * Via php mail() function
 *
 * @param string       $to   email to
 * @param string       $txt  text/html subject & text divided by \n
 * @param string       $from email from
 * @param string|array $file attachment(s) file path
 *
 * @return bool
 */
function xmail($to, $txt, $from, $file = '')
{
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    list($subj, $text) = explode("\n", $txt, 2);

    $subject = '=?UTF-8?B?' . base64_encode(trim($subj)) . '?=';
    $text = nl2br(trim($text));
    $from = trim($from);

    $heads = '';
    $body = '';
    $un = strtoupper(uniqid(time()));

    list($fmail, $from_name) = parse_mail_from($from);
    $heads .= "From: =?UTF-8?B?" . base64_encode($from_name) . "?= <$fmail>\n";

    $heads .= "X-Mailer: X Mail Tool\n";
    $heads .= "Reply-To: $fmail\n";
    $heads .= "Return-Path: $fmail\n";
    $heads .= "Mime-Version: 1.0\n";
    if (!empty($file)) {        //массив или строка
        if (!is_array($file)) {
            $files[] = $file;
        } else {
            $files = $file;
        }
        $heads .= "Content-Type:multipart/mixed; ";
        $heads .= "boundary=\"----------" . $un . "\"\n\n";
        $body .= "------------" . $un . "\nContent-Type:text/html; charset=UTF-8;  format=flowed; delsp=yes\n";
        $body .= "Content-Transfer-Encoding: 8bit\n\n$text\n\n";
        foreach ($files as $filename) {
            if (!file_exists($filename)) {
                continue;
            }
            $f = fopen($filename, "rb");

            if (!$f) {
                continue;
            }

            $body .= "------------" . $un . "\n";
            $body .= "Content-Type: application/octet-stream;";
            $body .= "name=\"" . basename($filename) . "\"\n";
            $body .= "Content-Transfer-Encoding:base64\n";
            $body .= "Content-Disposition:attachment;";
            $body .= "filename=\"" . basename($filename) . "\"\n\n";
            $body .= chunk_split(base64_encode(fread($f, filesize($filename)))) . "\n";
        }
    } else {
        $heads .= "Content-Type:text/html; charset=UTF-8;  format=flowed; delsp=yes\n";
        $body .= $text;
    }
    $log = "\t-- sending... $to, $subj ...";
    $b = mail($to, $subject, $body, $heads);
    $log .= ($b) ? 'OK' : "FAILED";
    echo($log);
    return $b;
}


function parse_mail_from($email)
{
    $re = "/[A-z0-9]+([-_\.]?[A-z0-9])*@[A-z0-9]+([-_\.]?[A-z0-9])*\.[a-z]+/";
    if (preg_match($re, $email, $matches)) {
        $fmail = $matches[0];
        $from_name = implode('', explode("<$fmail>", $from));
        return [$fmail, $from_name];
    } else {
        return false;
    }
}
