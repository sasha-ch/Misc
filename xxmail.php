/* кому, текст с темой, от, файл(ы) 
 * файл(ы): строка или массив */
function xxmail($to, $txt, $from = '', $file = '')
{
	if(!isEmail($to))
		return false;

	list($subj, $text) = explode("\n", $txt, 2);
	
	$from = !empty($from) ? trim($from) : MAIL_FROM;
	$subject = '=?UTF-8?B?' . base64_encode(trim($subj)) . '?=';
	$text = nl2br(trim($text));
	
	$heads = '';
	$body = '';
	$un = strtoupper(uniqid(time()));
	
	$fmail = getEmail($from);
	$from_name = implode('', explode("<$fmail>", $from));
	$heads .= "From: =?UTF-8?B?" . base64_encode($from_name) . "?= <$fmail>\n";
		
	$heads .= "X-Mailer: SeoS Mail Tool\n";
	$heads .= "Reply-To: $fmail\n";
	$heads .= "Return-Path: $fmail\n";
	$heads .= "Mime-Version: 1.0\n";
	if(!empty($file)){		//массив или строка
		if(!is_array($file)){
			$files[] = $file;
		}else{
			$files = $file;
		}
		$heads .= "Content-Type:multipart/mixed; ";
		$heads .= "boundary=\"----------" . $un . "\"\n\n";
		$body  .= "------------" . $un . "\nContent-Type:text/html; charset=UTF-8;  format=flowed; delsp=yes\n";
		$body  .= "Content-Transfer-Encoding: 8bit\n\n$text\n\n";
		foreach($files as $filename){
			$f = fopen($filename, "rb");
 
			$body .= "------------" . $un . "\n";
			$body .= "Content-Type: application/octet-stream;";
			$body .= "name=\"" . basename($filename) . "\"\n";
			$body .= "Content-Transfer-Encoding:base64\n";
			$body .= "Content-Disposition:attachment;";
			$body .= "filename=\"" . basename($filename) . "\"\n\n";
			$body .= chunk_split(base64_encode(fread($f, filesize($filename)))) . "\n";
		}
	}else{
		$heads     .= "Content-Type:text/html; charset=UTF-8;  format=flowed; delsp=yes\n";
		$body .= $text;
	}
	$log = "\t-- sending... $to, $subj ...";
	$b = mail($to, $subject, $body, $heads);
	$log .= ($b) ? 'OK' : "FAILED";
	if (!$b)
		errorlog ($log);
	else
		debuglog ($log);
	return $b;
}
