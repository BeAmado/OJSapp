<?php

require_once('helperFunctions.php');
require_once('testXml.php');

function initialsStats($xml) {
	$initials = $xml->getElementsByTagName('initials');
	$sizes = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, '6+' => 0);
	foreach($initials as $initial) {
		$size = strlen($initial->nodeValue);
		if ($size <= 6) {
			$sizes[$size]++;
		}
		else if ($size > 6) {
			$sizes['6+']++;
		}
		
		if ($size == 6) {
			echo "The initials " . $initial->nodeValue . " has length 6\n";
		}
		else if ($size > 6) {
			echo "The initials" . $initial->nodeValue . " has length greater than 6!!!\n";
		}
	}
	print_r($sizes);
}

$filename = readline("Enter the filename: ");
$data = myReadXml($filename);
$xml = $data['xml'];
initialsStats($xml);
