Twitter Signon Example
======================

Provides a sample implementation of an ajax-y Twitter Signon process, though its not actually ajax-y at all. Since the Oauth authentication requires handling by the php server, a new window is opened, which carries out the authentication and then sends its important information back to the opener window before closing itself. 

### Twitter Application

A twitter application is required to run this example. [Create one](https://dev.twitter.com/apps) on your own twitter account. The consumer key and consumer secret values that are generated will be used in configuration of this example.

### Env

After running `cp Env.example .env` in a terminal, be sure to set the environment variables it contains before running the page. 

    TWITTER_CONSUMER_KEY
    TWITTER_CONSUMER_SECRET
    ROOT_URL

### Run

To run the example, you'll need to run a php web server in the project directory or wherever, so long as the page is loaded in a browser through a web server. The `ROOT_URL` env var will have to match the root url for accessing the index.html page.

The simplest web server you can run is the one [built into PHP](http://php.net/manual/en/features.commandline.webserver.php), but you'll have to have version >= 5.4.x. 

    php -S localhost:8000
    
Another option is to run it through [MAMP](http://www.mamp.info/en/index.html). CD to your MAMP document root and either copy the example files there, or create a symbolic link:

    ln -s /path/to/twitter-signon-example

### Todos

- "Auth Check" -- get currently logged in user but don't prompt for login. This may have to be ajax to avoid new window opening. This method would typically be called when a page is loaded, not when a user takes action.
- Wrap errors up in proper objects to be sent back to the parent frame when something goes wrong in the auth process.
- Capture cancelation, reply to parent frame with that info
- Sign out functionality

Twitter Signon Example Using Hello.js
=====================================

A second example uses [Hello.js](http://adodson.com/hello.js), "A client-side Javascript SDK for authenticating with OAuth2 (and OAuth1 with a oauth proxy)". The current example uses OAuth1, and therefore the oauth proxy provided at [https://auth-server.herokuapp.com](https://auth-server.herokuapp.com).

See `hello.html`



