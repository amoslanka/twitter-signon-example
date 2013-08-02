<?php

// 
// TwitterTransport is intended to work as a mediator between a JavaScript 
// frontend and the Twitter API, particularly as a wrapper around TwitterOAuth. 
// This lib provides configuration and session credential storage more biased  
// to a real app, so in order to authenticate and call api methods with the 
// resulting tokens. This lib provides the link and configuration between the 
// TwitterOAuth lib and the application structures that require its connection.
// 

// 
// Actions available:
// 
// login
// 		Start the oauth process by requesting the temporary credentials.
// 
// verify 
// 		The callback from the temporary credentials call. Completes the oauth 
// 		process by saving the token credentials to session.
// 		Should not be used by anything other than the oauth callback triggered
// 		in the login action (automatic).
// 
// check
// 		Check whether the current session is valid. If credentials do not 
// 		exist, it will respond with HTTP 401 Unauthorized. If credentials
// 		do exist, they'll be sent to Twitter for verification, and twitter's
// 		response will be forwarded on. From the Twitter docs:
// 			Returns an HTTP 200 OK response code and a representation of the 
// 			requesting user if authentication was successful; returns a 401 
// 			status code and an error message if not.
// 
// logout
// 		Destroy the current session.
// 
// 
// Any action can be used by requesting this page with a `action=<action name>` 
// query string.
// 
// Additional param options
// 
// behavior
// 		
// 		popup
// 			Intended to work as a popup or _blank target window. Will send response
// 			data as a JavaScript object back to the method on `window.opener` defined 
// 			by the `callback` param and prompty close itself after completing its task. 
// 			
// 		proxy (default)
// 			Whatever response is the result of the task carried out by the action will
// 			be proxied as the response of the request to this action. 
// 
// callback
// 	
// 		If the behavior of the request is specified as a popup, the callback param defines
// 		the name of the method that will be called on the `window.opener` to return the 
//		response to the originator. Default: "twitter_transport_callback"
// 

// 
// TODOS
// 
// - Add prefixes to all session variables to avoid name conflicts.
// - Add support for multiple behavior types. (proxy)
// - Infer behaviors not from a query var but from the content type header.
// 

require_once("./lib/Dotenv.php");  
Dotenv::load('..');
require_once('./lib/OAuth.php');
require_once("./lib/twitteroauth.php");  

session_start(); 

// 
// The sensitive twitter keys to your the associated Twitter App should be stored in 
// the environment variables only. (Never in version control)
// 
define('CONSUMER_KEY', $_ENV['TWITTER_CONSUMER_KEY']);
define('CONSUMER_SECRET', $_ENV['TWITTER_CONSUMER_SECRET']);
define('ROOT_URL', empty($_ENV['ROOT_URL']) ? ((!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'] : "http://".$_SERVER['SERVER_NAME']) : $_ENV['ROOT_URL']);

// 
// The action parameter is our router of sorts. It determines what were doing in
// this request. 
if (empty($_REQUEST['action'])) { $_REQUEST['action'] = "check"; }
switch ($_REQUEST['action']) {
	case 'login':
		login();
		break;
	case 'verify':
		verify();
		break;
	case 'logout':
		logout();
		break;
	case 'check':
		check();
		break;
}

// 
// ACTIONS
// 


// 
// Start the oauth login process by requesting the temporary credentials.
// 
function login() {
	// TwitterOAuth instance
	$twitteroauth = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);  

	// Requesting authentication tokens, the parameter is the URL we will be redirected to  
	$request_token = $twitteroauth->getRequestToken($root_url.'/auth/twitter.php?action=verify');
	  
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
	    // TODO: add proper response type
	}  
}

// 
// Destroy the current session.
// 
function logout() {

	unset($_SESSION['token_credentials']);

	respond();
	
}

// 
//  Verify the users credentials and stow the user info to be passed back later.
// 
function verify() {
	// Check for the expected temporary credentials.
	if(empty($_GET['oauth_verifier']) || empty($_SESSION['oauth_token']) || empty($_SESSION['oauth_token_secret'])){  
	    // Something's missing, go back to the start of the login process. The 
	    // login action will set the required session values for us to proceed.
	    header('Location: twitter.php?action=login');  
	} 

	// TwitterOAuth instance, with the temporary credentials
	$twitteroauth = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);  

	// Request the oauth verification with the verifier that can be used only once.
	// What we get back is the token credentials that we can use as our session. As
	// long as we have these token_credentials, we won't have to log in again.
	$_SESSION['token_credentials'] = $twitteroauth->getAccessToken($_GET['oauth_verifier']); 

	// Check with the api if our credentials are valid. Response will be a json object
	// describing the user with a 200 status if successful, 401 with error messaging if not.
	$response = $twitteroauth->get('account/verify_credentials'); 

	// Respond to the local request accordingly.
	respond($response);
}

// 
// Check whether the current session is valid.
// 
function check() {
	// 
	// Not a step. Check if the user is logged in.
	// 

	if(empty($_SESSION['oauth_token']) || empty($_SESSION['oauth_token_secret'])){  
	    // Something's missing, go back to square 1  
	    header('Location: twitter.php?step=1');  
	} 

	// Pull our token credentials from the session
	$token_credentials = $_SESSION['token_credentials'];

	// TwitterOAuth instance, with sessioned parameters we got by authenticating
	$twitteroauth = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $token_credentials['oauth_token'], $token_credentials['oauth_token_secret']);  

	// Check with the api if our credentials are valid. 
	$response = $twitteroauth->get('account/verify_credentials'); 

	// Respond to the local request accordingly.
	respond($response);

	// TODO: add proper response statuses and proxy behavior for verify_credentials
}




// 
// HELPERS
// 

function respond($r) {

	// TODO: more response types

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
				var data = JSON.parse('<?php echo addslashes(json_encode($r));?>');
				window.opener.twitter_signin_callback(data);
				window.close();
			</script>
		</head>
	</html>
	<?php
}

?>

