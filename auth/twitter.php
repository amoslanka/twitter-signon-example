<?php

// 
// Intended to work as a popup or _blank target window. Will close itself after successfully carrying
// out the Oauth steps, even if there was a problem along the way. 
// 

require_once("./lib/Dotenv.php");  
Dotenv::load('..');
require_once('./lib/OAuth.php');
require_once("./lib/twitteroauth.php");  

session_start(); 

// 
// These should be stored in environment variables.
// 
$consumer_key = $_ENV['TWITTER_CONSUMER_KEY'];
$consumer_secret = $_ENV['TWITTER_CONSUMER_SECRET'];
$root_url = empty($_ENV['ROOT_URL']) ? ((!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'] : "http://".$_SERVER['SERVER_NAME']) : $_ENV['ROOT_URL'];

// Clean up our step variable
if (empty($_REQUEST['step'])) { $_REQUEST['step'] = 1; }
$_REQUEST['step'] = intval($_REQUEST['step']);
$response = '';

if ($_REQUEST['step'] == 1) {

	// 
	// Carry out step 1. Get the request token from Twitter.
	// 

	// TwitterOAuth instance
	$twitteroauth = new TwitterOAuth($consumer_key, $consumer_secret);  

	// Requesting authentication tokens, the parameter is the URL we will be redirected to  
	$request_token = $twitteroauth->getRequestToken($root_url.'/auth/twitter.php?step=2');
	  
	// Saving them into the session  
	$_SESSION['oauth_token'] = $request_token['oauth_token'];  
	$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];  
	  
	// We can expect a 200 response if everything goes well on twitter's side of things.  
	if($twitteroauth->http_code==200){  
	    // Let's generate the URL and redirect  
	    $url = $twitteroauth->getAuthorizeURL($request_token['oauth_token']); 
	    header('Location: '. $url); 
	} else { 
	    // It's a bad idea to kill the script, but we've got to know when there's an error.  
	    die('Something wrong happened.');  
	}  

} elseif ($_REQUEST['step'] == 2) { 

	// 
	// Carry out step 2. Verify the users credentials and stow the user info to be passed back later.
	// 

	if(empty($_GET['oauth_verifier']) || empty($_SESSION['oauth_token']) || empty($_SESSION['oauth_token_secret'])){  
	    // Something's missing, go back to square 1  
	    header('Location: twitter.php?step=1');  
	} 

	// TwitterOAuth instance, with two new parameters we got in twitter_login.php  
	$twitteroauth = new TwitterOAuth($consumer_key, $consumer_secret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);  
	// Let's request the access token  
	$access_token = $twitteroauth->getAccessToken($_GET['oauth_verifier']); 
	// Save it in a session var 
	$_SESSION['access_token'] = $access_token; 
	// Let's get the user's info 
	$response = $twitteroauth->get('account/verify_credentials'); 

}

header('Content-Type: text/html');

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Twitter Signin</title>
		<script type="text/javascript">
			<?php
			// Print the data to js, then send it back to the parent window and
			// close self. 
			?>
			var data = JSON.parse('<?php echo addslashes(json_encode($response));?>');
			window.opener.twitter_signin_callback(data);
			window.close();
		</script>
	</head>
</html>
