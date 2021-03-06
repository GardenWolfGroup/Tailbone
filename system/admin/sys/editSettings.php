<?PHP
	
	//Make sure everything is running in the proper place at the proper time and in the proper way
	if(!$runningInIndex){
		header('HTTP/1.0 403 Forbidden');
		die('403 FORBIDDEN: You are not allowed to access that file outside its normal running location.');
	}
	
	//See previous comment, a piece of code just in case (paranoid Cody)
	if(!$allowRequest){
		$_SESSION['MSGBanner'] = 'Unknown error.';
		$_SESSION['MSGType'] = 3;
		header('location:./?admin');
		die('403 FORBIDDEN: TailBone did not allow the requested action to be preformed.');
	}
	
	session_start();
	
	//Makes sure that the user wasn't a URL copying pain in the butt and actually is logged into the system
	if(!$loggedin){
		$_SESSION['MSGBanner'] = 'You must be logged in to do that!';
		$_SESSION['MSGType'] = 2;
		header('location:./?admin');
		die();
	}
	
	$_POST['siteDescription'] = htmlspecialchars($_POST['siteDescription']);
	
	if(isset($_POST['construction'])){
		$construction = 'true';
	}else{
		$construction = 'false';
	}
	
	$data = '
<?PHP
	$settings = array(
		\'siteName\' => \''.addslashes(htmlspecialchars($_POST['siteName'])).'\',
		\'siteDescription\' => \''.addslashes(htmlspecialchars($_POST['siteDescription'])).'\',
		\'siteKeywords\' => \''.addslashes(htmlspecialchars($_POST['siteKeywords'])).'\',
		\'siteAuthor\' => \''.addslashes(htmlspecialchars($_POST['siteAuthor'])).'\',
		\'loginNotice\' => \''.addslashes(htmlspecialchars($_POST['loginNotice'])).'\',
		\'analyticsCode\' => file_get_contents("./data/analyticsCode.php"),
		\'footerContent\' => \''.addslashes(htmlspecialchars($_POST['footerContent'])).'\',
		\'adContent\' => file_get_contents("./data/adContent.php"),
		\'construction\' => '.$construction.',
		\'four04Message\' => \''.addslashes(htmlspecialchars($_POST['four04Message'])).'\',
	);
?>
	';
	
	//Write to the settings file
	$location = './data/settings.php';
	$content = fopen($location, 'w');
	fwrite($content,$data);
	fclose($content);
	
	//The advertisements that everyone loves!
	$location = './data/adContent.php';
	$content= fopen($location, 'w');
	fwrite($content,$_POST['adContent']);
	fclose($content);
	
	//And the analytics update so that Google can get into another part of your life
	$location = './data/analyticsCode.php';
	$content= fopen($location, 'w');
	fwrite($content,$_POST['analyticsCode']);
	fclose($content);
	
	$_SESSION['MSGBanner'] = 'Successfully edited settings.';
	$_SESSION['MSGType'] = 1;
	header('location:./?admin&page=settings');
	die();
?>