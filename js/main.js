$(document).ready(function(){

	window.signin = function () {
		var popup = window.open("auth/twitter.php", "twitter_signin");

		// Set up a callback for our popup to send its information home.
		window.twitter_signin_callback = function(data) {

			// Destroy the callback so we are not leaving methods out there 
			// in the global space.
			window.twitter_signin_callback = null;

			// (assuming success)
			$('.result').html('<div class="alert">You are logged in as ' + (data.name || data.screen_name) + '</div>');

		}
	};

	$('a.signin').click(function(event){
		signin();
		return false;
	});

});