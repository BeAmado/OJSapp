<?php

require_once('cleanEncoding.php');

/**
this function tries to read the xml file
returns an array containing:
	the xml root node as key 'xml'
	the number of tries when loading the xml as 'tries'
	the encoding used to open the xml as 'encoding'
*/
function myReadXml($filename) {
	$tries = 0;
	$encodings = array('utf-8', 'iso-8859-1', 'windows-1252');
	foreach ($encodings as $encoding) {
		$tries++;
		$xml = new DOMDocument('1.0', $encoding);
		echo "\nTrying to load with charset $encoding .....";
		$loadedXml = $xml->load($filename);
		if ($loadedXml) {
			echo " success\n";
			return array('xml' => $xml, 'encoding' => $encoding, 'tries' => $tries);
		}
		else {
			echo " failed\n";
		}
	}

	$tries++;
	$xml = new DOMDocument('1.0', 'utf-8');
	$strXml = file_get_contents($filename);
	if ($xml->loadXml(utf8_encode($strXml))) {
		return array('xml' => $xml, 'encoding' => 'utf-8', 'tries' => $tries);
	}
	else {
		exit("\nCould not open the file '$filename'.\n");
	}
	
}

//this function processes every node of the xml to translate the encoding
function xmlFix(&$xml, &$nodesProcessed, $isEmbed = false) {
	if ($isEmbed) {
		echo "\nUnmessing the embed.\n";
		if ($xml->hasChildNodes()) {
			$embedChildren =& $xml->childNodes;
			echo "\nThe embed has children\n";
			foreach($embedChildren as $embedChild) {
				$embedChild->nodeValue = unmessEncoding($xml->nodeValue, false);
				$nodesProcessed++;
			}
		}
		else {
			$xml->nodeValue = unmessEncoding($xml->nodeValue, false);
			$nodesProcessed++;
		}
	}
	else if ($xml->hasChildNodes()) {
		$children =& $xml->childNodes;
		foreach($children as $child) {
			if ($child->nodeName == 'embed') {
				xmlFix($child, $nodesProcessed, true);
			}
			else {
				xmlFix($child, $nodesProcessed);
			}
		}
	}
	else {
		$xml->nodeValue = unmessEncoding($xml->nodeValue);
		$nodesProcessed++;
		if ($nodesProcessed % 10000 == 0) {
			echo "\nProcessed $nodesProcessed nodes.";
		}
	}
}

/**
function to open the xml file and optionally fix the encoding
*/
function processFile($filename, $fixEncoding = false) {

	$contentType = null;
	if (strpos($filename, 'issue') !== false) {
		$contentType = 'issues';
	}
	else if (strpos($filename, 'user') !== false) {
		$contentType = 'users';
	}
	else {
		echo "\nThe file is not 'issues' nor 'users'\n";
		return false;
	}
		
	$data = myReadXml($filename);
	echo "\n\nIt needed " . $data['tries'] . " tries to load the file '$filename'.\nThe encoding used to open is " . 
	$data['encoding'] . ".\n\n";
	
	$xml = $data['xml'];
	$content =& $xml->getElementsByTagName($contentType)->item(0);
	$processedNodes = 0;

	if ($fixEncoding) {
		xmlFix($content, $processedNodes);
		$newFilename = 'fixed_' . $filename;
		if ($processedNodes > 0 && $xml->save($newFilename)) {
			echo "\n\nSuccessfully saved the file '$newFilename'.\n";
			echo "Processed nodes: $processedNodes\n\n";
		}
		else if ($processedNodes == 0) {
			echo "\n\nDid not process any node.\n\n";
		}
		else {
			echo "\n\nCould not save the file '$newFilename'. \n\n";
		}
	}
	else {
		$count = 0;
		foreach ($content->childNodes as $child) {
			$count++;
			//echo "\nThe node name is " . $child->nodeName;
		}
		echo "\n\nThere are $count $contentType.\n\n";
	}
}

/*$filename = readline('Enter the filename of the xml to be read: ');
$fix = readline('Do you want to fix the encoding? (y/N) : ');
if (strtolower($fix) === 'y' || strtolower($fix) === 'yes') {
	processFile($filename, true);
}
else {
	processFile($filename);
}*/
/*$handle = fopen($filename, 'rb');
$line1 = fgets($handle);
$line2 = fgets($handle);
$line3 = fgets($handle);
$line4 = fgets($handle);
$line5 = fgets($handle);
$line6 = fgets($handle);

echo "\nThe size of line1 is " . strlen($line1);
echo "\nThe size of line2 is " . strlen($line2);
echo "\nThe size of line3 is " . strlen($line3);
echo "\nThe size of line4 is " . strlen($line4);
echo "\nThe size of line5 is " . strlen($line5);
echo "\nThe size of line6 is " . strlen($line6) . "\n\n";


fclose($handle);*/
