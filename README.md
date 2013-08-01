Twitter Signon Example
======================

Provides a sample implementation of an ajax-y Twitter Signon process, though its not actually ajax-y at all. Since the Oauth authentication requires handling by the php server, a new window is opened, which carries out the authentication and then sends its important information back to the opener window before closing itself. 

### Env

After running `cp Env.example .env` in a terminal, be sure to set the environment variables it contains before running the page. 

	TWITTER_CONSUMER_KEY
	TWITTER_CONSUMER_SECRET
	ROOT_URL

### Todos

- "Auth Check" -- get currently logged in user but don't prompt for login. This may have to be ajax to avoid new window opening. This method would typically be called when a page is loaded, not when a user takes action.
- Wrap errors up in proper objects to be sent back to the parent frame when something goes wrong in the auth process.
- Capture cancelation, reply to parent frame with that info
- Sign out functionality