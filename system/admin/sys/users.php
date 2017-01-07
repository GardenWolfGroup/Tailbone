<?PHP
	//Validate the running page  (should be index.php in the root of the webserver)
	if(!$runningInIndex){
		header('HTTP/1.0 403 Forbidden');
		die('403 FORBIDDEN: You are not allowed to access that file outside its normal running location.');
	}
	
	//Cody is still being paranoid.
	if(!$allowRequest){
		header('location:./?admin&MSGBanner=Unknown error.');
		die('403 FORBIDDEN: TailBone did not allow the requested action to be preformed.');
	}
	
	session_start();
	
	//looks to see if the user is logged in.
	if(!$_SESSION['loggedin']){
		header('location:./?admin&MSGBanner=You must be logged in to do that!&MSGType=3');
		die();
	}

	function writeUserData($messageOnCompletion){
		//getting global vars.
		global $users;
		global $serverVars;
		global $hosted;
		
		//initiates the user data.
		$userData = "";
		
		//for each of the users in the users aray. Write them.
		
		//makes sure the server admin does not get written to the file.
		if($hosted){
			$spare = $serverVars['serverAdmin'];
		}else{
			$spare = "";//HACKHACKHACK
		}
		
		//starts building the data for the file.
		foreach($users as $key => $value){
			if($key != $spare){
				$userData = $userData."'".$key."' => '".$value."',\n";
			}
		};
		
		//finish making the data.
		$data = "<?php\n \$users=array(\n".$userData."\n);\n ?>";
		
		//writes the file.
		$location='./data/users.php';
		$content = fopen($location,'w');
		fwrite($content, $data);
		fclose($content);
		sleep(3); //HACKHACKHACK
		
		//gets the contents of the file
		$dataCheck = file_get_contents('./data/users.php');
		
		//checks that the file was written correctly.
		if($dataCheck == $data){
			header('location:./?admin&MSGBanner='.$messageOnCompletion.'&page=users&MSGType=1');
			die();
		}else{
			header('location:./?admin&MSGBanner=Unknown error, please check perms.&page=users&MSGType=3');
			die();
		}
	}
	
	//looks to see if the user would like to edit a user.
	if($_GET['intent'] == "edit"){
		//looks to see if you are a server admin.
		if($hosted && $_SESSION['user'] == $serverVars['serverAdmin']){
			//gets the user that you wish to edit.
			$user = $_POST['user'];
			//sets the new password.
			$newPass = $_POST['newPass'];
			//gets the verified password.
			$newPassVerify = $_POST['newPassVerify'];
			
			//checks that the passwords match.
			if($newPass == $newPassVerify){
				//sets the password encryption options.
				$options = [
				    'cost' => 12,
				];
				
				//sets the user and password..
				$users[$user] = password_hash($newPass,PASSWORD_BCRYPT, $options);
				
				//calls up the write function from above.
				writeUserData('Successfully changed '.ucfirst($user)."'s".' password!');
			}else{
				//failsafe to make sure the password ARE correct. this might come in handy if JS breaks. (I.E, IE happens.)
				header('location:./?admin&MSGBanner=Passwords do not match.&page=users&MSGType=2');
				die();
			}
		}else{
			//if you are not a server admin, you are a user.
			$user = $_SESSION['user'];
			
			//gets your current password.
			$currentPass = $users[$user];
			//gets the password that you think is your current password.
			$oldPass = $_POST['oldPass'];
			//gets the password that you would like to use.
			$newPass = $_POST['newPass'];
			//gets the verify.
			$newPassVerify = $_POST['newPassVerify'];
			
			//if your old password is in fact correct. Go ahead and run.
			if(password_verify($oldPass, $currentPass)){
				//looks to see if your passwords match.
				if($newPass == $newPassVerify){
					//setting the options for password encryption.
					$options = [
					    'cost' => 12,
					];
					//sets the user data.
					$users[$user] = password_hash($newPass,PASSWORD_BCRYPT, $options);
					//writing the user data.
					writeUserData('Successfully changed password!');
				}else{
					//same as before.
					header('location:./?admin&MSGBanner=Passwords do not match.&page=users&MSGType=2');
					die();
				}
			}else{
				//oops, you forgot your password.
				header('location:./?admin&MSGBanner=Old pass does not match current password.&page=users&MSGType=2');
				die();
			}
		}
		
		//OH LOOK, they want to make a user.
	}elseif($_GET['intent'] == 'create'){
		//again with the options...
		$options = ['cost'=>12];
		//makes the user name all lower case.
		$_POST['name'] = strtolower($_POST['name']);
		
		if(isset($users[$_POST['name']])){
			//does the user already exist?
			header('location:./?admin&MSGBanner=The user "'.ucfirst($_POST['name']).'" already exists!&page=users&MSGType=2');
		}else{
			//sets the user.
			$users[$_POST['name']] = password_hash($_POST['pass'],PASSWORD_BCRYPT, $options);
			//writes the users.
			writeUserData('User "'.ucfirst($_POST['name']).'" added!');
		}
	}elseif($_GET['intent'] == 'delete'){
		//just get rid of the user.
		unset($users[$_GET['user']]);
		//we dont care, kick him out of the house!
		writeUserData('Successfully deleted user "'.ucfirst($_GET['user']).'".&MSGType=1');
	}
?>