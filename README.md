mod_auth_digest-emulator
========================

Code to emulate the handling of Apache's mod_auth_digest in PHP.

## Why we wrote it?
* In our huge cpanel shared environment, mod_auth_digest is not enabled by default on our server configurations.
* On our cloud environment, it is enabled. However, due to a bug in litespeed, it didn't work most of the time.
* We thought it would be fun to (re)learn how digest auth works again

## How it works:
* We rewrite all requests to a php file, except for a specific IP (ie a proxy or loopback ip)
* We then use this php file to auth the client
* If successful, it will use curl to connect locally or via a proxy and retrieve the original file (the original rewrites allow this request and doesn't redirect it to the php file)


Yeah, We could have used FastCGI, however this again is not enabled in our shared environment

## Requirements:
* Webserver with ModRewrite like rewriting
* PHP with cURL

## Known Bugs:
* Multiple realms in htpasswd files are not supported