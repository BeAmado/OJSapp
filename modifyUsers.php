<?php

function fixDuplicateInterests(&$user) {
	$username = $user->getElementsByTagName('username')->item(0)->nodeValue;
	$duplicateInterests = 0;
	$interests = array();
	$interestsNodes = $user->getElementsByTagName('interests');
	$in = '';
	foreach($interestsNodes as $interest) {
		array_push($interests, $interest);
	}
	foreach($interests as $node) {
		if (htmlentities($node->nodeValue) === htmlentities($in)) {
			$duplicateInterests++;
			$node->parentNode->removeChild($node);
		}
		$in = $node->nodeValue;
	}
	
	if ($duplicateInterests) {
		echo "\nUsuário $username com $duplicateInterests interests duplicados\n";
	}
}
//FIM DE fixDuplicateInterests


function fillImportUsersInfo(&$xmlNode, $arr) {
	//$arr é um array com os dados necessários
	
	
		////////// XML SCHEMA ///////////////////////////
		//<!--<import_users_info>
		//	  <journal>
		//		  <name>
		//	  </journal>
		//	  <changed_users>
		//		  <users>...</users>
		//		  <num_changed_users>
		//		  <num_changed_users_registered>
		//	  </changed_users>
		//	  <num_users_original>
		//	  <num_users_registered>
		//</import_users_info>-->
		/////////////////////////////////////////////////
	
	
	$import_users_info = $xmlNode->createELement('import_users_info'); //the root node
		
	///////////////////// //////////////////////////////
	$exportedJournalNameNode = $xmlNode->createElement('name', $arr['exportedJournalName']); 
	
	$journalNode = $xmlNode->createElement('journal');
	
	$journalNode->appendChild($exportedJournalNameNode);
	/////////////////////////////////////////////////////////////////////////
	
	
	///////////////////////////////// /////////////////////////////////////////////////////////
	$changed_usersNode = $xmlNode->createElement('changed_users'); //
	
	$num_usersNode = $xmlNode->createElement('number_of_users', $arr['num_user_changes']);
	$changed_usersNode->appendChild($num_usersNode);
	
	$num_users_registeredNode = $xmlNode->createElement('changed_users_already_registered', $arr['num_changed_users_registered']);
	$changed_usersNode->appendChild($num_users_registeredNode);
	
	$changed_usersNode->appendChild($arr['users']);
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	/////////////////// creating the nodes to store the numbers of users  ///////////////////
	
	$num_users_originalNode = $xmlNode->createElement('num_users_original', $arr['num_users_original']);
	$num_users_registeredNode = $xmlNode->createElement('num_users_already_registered', $arr['num_users_registered']);
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	////////////////  PREENCHENDO IMPORT_USERS_INFO E COLOCANDO-O COMO RAIZ DO DOCUMENTO ///////////////////////////////////
	
	$import_users_info->appendChild($num_users_originalNode);
	$import_users_info->appendChild($num_users_registeredNode);
	$import_users_info->appendChild($journalNode);
	$import_users_info->appendChild($changed_usersNode);
	
	$xmlNode->appendChild($import_users_info);
}
// FIM DE fillImportUsersInfo


function addChangedUser(&$changedUsersNode, &$xmlNode, $userInfo) {

	$username_node = $xmlNode->createElement('username');
	$username_new = $xmlNode->createElement('new', $userInfo['new_username']);
	$username_old = $xmlNode->createElement('old', $userInfo['username']);
	$username_node->appendChild($username_old);
	$username_node->appendChild($username_new);
	
	$firstname_node = $xmlNode->createElement('firstname', $userInfo['firstname']);
	$middlename_node = $xmlNode->createElement('middlename', $userInfo['middlename']);
	$lastname_node = $xmlNode->createElement('lastname', $userInfo['lastname']);
	$registered_node = $xmlNode->createElement('already_registered', $userInfo['registered']);
	$email_node = $xmlNode->createElement('email', $userInfo['email']);
	
	$user_changed = $xmlNode->createElement('user');
	$user_changed->appendChild($firstname_node);
	$user_changed->appendChild($middlename_node);
	$user_changed->appendChild($lastname_node);
	$user_changed->appendChild($username_node);
	$user_changed->appendChild($email_node);
	$user_changed->appendChild($registered_node);
	
	//GUARDAR O user_changed NOS USERS DO ARQUIVO username_changes.xml
	$changedUsersNode->appendChild($user_changed);
}
//FIM DE addChangedUser


function processUsers($user, &$changedUsers, &$arrInfo, &$xmlNode, &$conn, &$errors) {
	
	$arrInfo['num_users_original']++;
	$username = $user->getElementsByTagName('username')->item(0)->nodeValue;
	
	//fixing the duplicate interests for the user 
	fixDuplicateInterests($user);
	////////////////////////////////////////////////////////////////////////////////////
	
	$middlename = '';
	$firstname = $user->getElementsByTagName('first_name')->item(0)->nodeValue;
	$lastname = $user->getElementsByTagName('last_name')->item(0)->nodeValue;
	if ($user->getElementsByTagName('middle_name')->length == 1)  $middlename = $user->getElementsByTagName('middle_name')->item(0)->nodeValue;
	$email = $user->getElementsByTagName('email')->item(0)->nodeValue;
	$newUsername = '';
	$registered = 0;
	
	$changeUsername = false;
	
	
	$selectUserDataByEmailSTMT = $conn->prepare('SELECT first_name, middle_name, last_name, email, username FROM users WHERE email = :email');
	$selectUsernameCountSTMT = $conn->prepare('SELECT COUNT(*) as count FROM users WHERE username = :username');
	
	$selectUserDataByEmailSTMT->bindParam(':email', $email, PDO::PARAM_STR);
	
	$newErrors = array();
	
	if ($selectUserDataByEmailSTMT->execute()) {
		
		if ($userData = $selectUserDataByEmailSTMT->fetch(PDO::FETCH_ASSOC)) {
			$registered = 1;
			$arrInfo['num_users_registered']++;
			if ($userData['username'] !== $username) {
				$changeUsername = true;
				$newUsername = $userData['username'];
				$arrInfo['num_changed_users_registered']++;
			}
		}
		else { // the user is not registered in the database
			
			$usernameOk = false;
			$num = 1;
			$usernameNumeric = false; 
			if (is_numeric(substr($username, -1))) {
				$usernameNumeric = true;
				$num = (int) substr($username, -1);
			}
			
			$newUsername = $username;
			
			////// testing if the username is already being used by someone else /////////////////
			while (!$usernameOk) {
				
				$selectUsernameCountSTMT->bindParam(':username', $newUsername, PDO::PARAM_STR);
				
				if ($selectUsernameCountSTMT->execute()) {
					$count = $selectUsernameCountSTMT->fetchColumn();
					if ($count > 0) {
						$changeUsername = true;
					}
					else {
						$usernameOk = true;
						break; //breaking out of the while !usernameOk
					}
				}
				else { // selectUsernameCountSTMT did not execute
					$error = array(
						'error' => 'selectUsernameCountSTMT did not execute', 
						'query' => 'SELECT COUNT(*) as count FROM users WHERE username = :username',
						'username' => $newUsername,
						'errorInfo' => $selectUsernameCountSTMT->errorInfo()
					);
					
					array_push($newErrors, $error);
				}
				
				if (!$usernameNumeric) {
					//if this condition is executed it will be only in the first iteration
					$newUsername .= $num;
					$usernameNumeric = true;
				}
				
				$num++; //increment the number at the end of the username
				$newUsername = substr($newUsername, 0, -1) . $num; //form the newUsername by incrementing the number at the end
				//echo "\n\nThe next test will be '$newUsername'\n\n";
				
				
			}//end of the while !usernameOK
			////// end of the test if the username is already being used ////////////////////////////
			
			
		}//end of the else indicating that the user is not in the database
		
	}//end of the if selectUserDataByEmailSTMT executed
	else {
		//the selectUserDataByEmailSTMT did not execute
		$error = array(
			'error' => 'selectUserDataByEmailSTMT did not execute', 
			'query' => 'SELECT first_name, middle_name, last_name, email, username FROM users WHERE email = :email',
			'email' => $email,
			'errorInfo' => $selectUserDataByEmailSTMT->errorInfo()
		);
		
		array_push($newErrors, $error);
	}
	
	
	//++++++++++++++++ SE PRECISAR MUDAR O USERNAME +++++++++++++++++++++++++++++++++++++++++++++++++++++
	if ($changeUsername) {
		//COLOCANDO O USER NO ARQUIVO username_changes.xml///////////////////////////////////////////////
		
		$userInfo = array(
			"new_username" => $newUsername,
			"username" => $username,
			"firstname" => $firstname,
			"middlename" => $middlename,
			"lastname" => $lastname,
			"registered" => $registered,
			"email" => $email
		);
		addChangedUser($changedUsers, $xmlNode, $userInfo);
		$arrInfo["num_user_changes"]++;
		//////////////////////////////////////////////////////////////////////////////////////////////////
	}
	//+++++++++++++++++ FIM DO IF changeUsername ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
	if (!empty($newErrors)) {
		array_push($errors, $newErrors);
	}
}
//FIM DE processUsers

function getIndentationFile() {
	$indentFile = "indent.xsl";
	$cwd =  getcwd();
	$maxTries = 3;
	$try = 0;

	while ($try < $maxTries) {
		$try++;

		$ls = scandir($cwd);

		if (in_array($indentFile, $ls)) {
			break;
		}
		else if (in_array("OJSapp", $ls)){
			$cwd .= "/OJSapp";
		}
		else {
			echo "\nCould not locate the .xsl file for indentation.\n";
			$indentFile = readline("Please enter the name of the indentation .xsl file with its path: ");
			break;
		}
	}

	if ($try >= $maxTries) echo "\nReached maximum number of tries.\n";
	return "$cwd/$indentFile";
}


function modify($filename) {
	
	$db_host = readline("Digite o host do banco de dados do ojs de destino: ");
	$db_username = readline("Digite o username do banco de dados do ojs de destino: ");
	$db_password = readline("Digite o password do banco de dados do ojs de destino: ");
	$db_name = readline("Digite o nome do banco de dados do ojs de destino: ");
	
	$error = "";
	
	$xml = new DOMDocument;

	$xml_username_changes = new DOMDocument('1.0', 'utf-8');
	$users = $xml_username_changes->createElement("users"); // node to keep the changes
	
	if (!$xml->load($filename)) {
		echo "\nUsando encoding iso-8859-1 para abrir o xml...\n";
		$strXml = file_get_contents($filename);
		//$xml = new DOMDocument('1.0', 'iso-8859-1');
		if (!$xml->loadXml(utf8_encode($strXml))) {
			exit("\nNão foi possível abrir o arquivo $filename.\n");
		}
	}
	
	require_once('helperFunctions.php');
	$conn = myConnect($db_host, $db_username, $db_password, $db_name); //from helperFunctions.php
	
	echo "\nMudando os usernames que já estão em uso no banco de dados $db_name...\n";
	
	$userNodes = $xml->getElementsByTagName("user");

	
	$info = array(
		"num_users_original" => 0, //the total number of users in the users.xml file
		"num_user_changes" => 0, //number of user that changed the username
		"num_users_registered" => 0, //the number of users in the users.xml file that were alredy registered
		"num_changed_users_registered" => 0 //number of users that were already registered and needed to change the username
	);
	
	$errors = array();
	
	//LOOP TO GO THROUGH EACH USER IN THE users.xml FILE///////////////////////////////////////
	foreach ($userNodes as $user) {
		processUsers($user, $users, $info, $xml_username_changes, $conn, $errors);
		//$user is the current user in the loop
		//$users is the  XmlNode
	}
	//END OF THE LOOP
	
	//CLOSING THE CONNECTION
	$conn = null;
	
	if (!empty($errors)) {
		echo "\n\nThe following errros occurred: \n\n";
		print_r($errors);
		echo "\n\n";
	}
	
	// Will only create the xml files if there is at least one user who needed change the username
	if ($info["num_user_changes"] > 0) {

		$changed_filename = "changed_$filename";
		echo "The users will be saved in the file $changed_filename.";
		
		$keep_filename = readline("Do you wish to keep this filename? (Y/n)");
		
		if ($keep_filename === "N" || $keep_filename === "n") {
			$changed_filename = readline("\nEnter the name you want for the file: ");
		}
		
		if ($xml->save("$changed_filename")) {
			echo "\nUsernames successfully changed and saved in the file $changed_filename.\n";
		}
		else {
			$error .= "\nCould not save the file $changed_filename.\n";
		}
		
		$exportedJournalName = readline("Enter the name of the journal: ");
		
		$arrayImportUsersInfo = array(
			"num_user_changes" => $info["num_user_changes"],
			"num_changed_users_registered" => $info["num_changed_users_registered"],
			"users" => $users,
			"num_users_original" => $info["num_users_original"],
			"num_users_registered" => $info["num_users_registered"],
			"exportedJournalName" => $exportedJournalName
		);
		
		
		///////////  CRIANDO O ARQUIVO de username_changes.xml ///////////////////////////////////////////////
		fillImportUsersInfo($xml_username_changes, $arrayImportUsersInfo);
		
		$indentFile = getIndentationFile(); //the previous function in this same script

		$xsl = new DOMDocument;
		$xsl->load($indentFile);
		
		$proc = new XSLTProcessor;
		
		$proc->importStyleSheet($xsl);
		
		$newfilename = $exportedJournalName."_username_changes.xml";
		
		echo "\nOs usuários serão salvos no arquivo $newfilename.";
		
		$keep_filename = readline("Deseja manter este nome de arquivo (S/n)? ");
		
		if ($keep_filename === "N" || $keep_filename === "n") {
			$newfilename = readline("\nDigite o nome que deseja para o arquivo: ");
		}
		
		if (file_put_contents("$newfilename", $proc->transformToXML($xml_username_changes))) {
			echo "\n$newfilename criado com sucesso!\n";
		}
		else {
			$error .= "Não foi possível criar o arquivo $newfilename\n";
		}
		/////////////////////////////////////////////////////////////////////////////////////////////////////////
		
	}//end of the if num_changes > 0
	
	else {
		$error = "Não há nenhum username repetido";
	}
	
	if ($error === "") {
		return true;
	}
	else {
		echo "\n$error";
		return false;
	}
}
//FIM DE modify

$users_file = readline("Enter the name of the .xml file with the users: ");
modify($users_file);
