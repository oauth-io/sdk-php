OAuth.io PHP SDK
================

OAuth that just works !

This SDK allows you to use OAuth.io's server-side flow from a PHP backend, to handle access tokens from your server instead of directly from your front-end.

You can use it with one of our front-end SDKs ([JavaScript][1], [PhoneGap][2], [iOs][3], [Android][4]), which will handle the user input for the OAuth flow.

The current version of the SDK is `0.2.0`. Older versions are deprecated.

You can also get nightlies by checking out our `develop` branch.

Common use-Case
---------------

You don't want to use APIs directly from the front-end, but rather through web-services inside your PHP backend.

The server-side flow
--------------------

In the server-side OAuth authentication flow, the oauth token never leaves your backend.

To authenticate a user, the flow follows these steps :

- Ask the backend for a unique state token. This token will be used for communicating with oauth.io
- Show a popup or redirect your user to request his permission to use his/her account on the requested provider
- The latter gives you a code, that you give to your backend
- The backend sends the code to oauth.io with other information like the oauth.io app's public key and secret.
- oauth.io responds with the access_token, that you can then store on your backend as long as it's valid
- You can then make requests to the API using that access token, directly from your backend

As of `0.2.0` it is possible to get an automatically refreshed access token when a refresh token is available.

Installation
------------

You can install it through Composer by adding the following dependency to your composer.json :

```json
 "require": {
        ...
        "oauth-io/oauth": "0.2.0"
        ...
    },
```

Then run in the console :

```sh
$ composer install
```

How to use it ?
---------------

The `OAuth` class is stored in the `OAuth_io` namespace. You need to include it in your file like this (make sure you have required the Composer autoloader file) :

```php
<?php

require_once '/path/to/autoload.php';

use OAuth_io\OAuth;

//?>
```

**PSR-0 support**

If you're using Composer with an autoloader, you can use the PSR-0 notation to use this package. Just put the following code at the top of your script :

```php
<?php

use OAuth_io\OAuth;

//?>
```

**Initialization**

To initialize the SDK, you have to give it your OAuth.io's app's key and secret (you can grab them on the [oauth.io Key-Manager](https://oauth.io/key-manager)) :

```php
<?php
$oauth = new OAuth();
$oauth->initialize('your_key', 'your_secret');
//?>
```

*Note on session*

You can give your own managed session array to the constructor so that if you already have a session manager, the SDK doesn't mess around with it :

```php
<?php
$_SESSION['some_subarray_in_the_session'] = array();
$myarray = $_SESSION['some_subarray_in_the_session'];

$oauth = new OAuth($myarray);
//?>
```

*Note on certificates*

If you're using [oauthd](https://github.com/oauth-io/oauthd) (the open source version of [oauth.io](https://oauth.io)) and that you don't have a verified ssl certificate yet (you should in the future if you want to put your code in production), you can disable the SSL certificate verification like this :

```php
<?php
$oauth = new OAuth(null, false);
//?>
```

**Generating a token**

You need to provide your front-end with a state token, that will be used to exchange information with OAuth.io. To generate it in the back-end :

```php
<?php
$token = $oauth->generateStateToken();
//?>
```

The `generateStateToken()` method returns a unique token. This token is stored in the session, and used to communicate with oauth.io.

You have to give this token to your front-end, where you can show the user a popup for him to log in to the provider and accept your app's permissions (see further down to see how to do that).

**Auth the user**

To be able to make requests to a provider's API using its access token, you have to call the `auth(provider, options)` method first. This method creates a request object from either a code you got from the front-end SDK (for the first time authentication), the session (if the user was authenticated during the same session), or a credentials array that you saved earlier.

To get a request object from a code (which automatically fills up the session for further use in other endpoints), you can do like this :

```php
$request_object = $oauth->auth('the_provider', array(
    'code': $code
));
```

`$request_object` is an object that allows you to perform requests (see further down to learn how to), and that contains the user's credentials.

You can get the credentials array if you need to save them for later use (or for a cron) like this :

```php
$credentials = $request_object->getCredentials();
```

The `$credentials` array contains the access token, refresh token and other information returned by the provider.

**Retrieving a code from the front-end**

```JavaScript
//In the front end, using the JavaScript SDK :

OAuth.initialize('your_key');
OAuth.popup('a_provider', {
        state: 'the_token_retrieved_from_your_backend'
    })
.done(function (r) {
    //You need to give r.code to your backend
    $.ajax({
            url: '/auth_endpoint/signin',
            data: {
                code: r.code
            }
    })
    .done(function (data, status) {
        //your user is authenticated server side
        //you can now call endpoints that use the OAuth.io SDK
    });
});
```

**Making requests to the API**

Once you have a request object, you can make requests to the API.

```php
<?php
$response_GET = $request_object->get('https://theprovider.com/api/endpoint');

$response_POST = $request_object->post('https://theprovider.com/api/endpoint', array('some' => 'data'));
$response_PUT = $request_object->put('https://theprovider.com/api/endpoint', array('some' => 'data'));
$response_DELETE = $request_object->del('https://theprovider.com/api/endpoint');
$response_PATCH = $request_object->patch('https://theprovider.com/api/endpoint', array('some' => 'data'));
//?>
```

You can also call the `me(array $filters)` method from that request object. This method returns a unified array containing information about the user.

```php
<?php
$facebook_requester = $oauth->create('facebook');
$result = $facebook_requester->me(array('firstname', 'lastname', 'email'));

// you'll have $result["firstname"], $result["lastname"] and $result["email"] set with the user's facebook information.
//?>
```

You can refer to the OAuth.io me() feature to get more information about the fields that are returned by this method.

**Using the session**

Usually, you'll want to make calls to the API several times while the user is connected to your app. Once you've authenticated the user once with a code, the session is automatically configured to work with the SDK.

Thus, you just need to do this to get a request object:

```php
$request_object = $oauth->auth('the_provider');
```

**Saving credentials**

You can also save the user's credentials to make requests in a cron. You can get the credentials array from a request object like this :

```php
$credentials = $request_object->getCredentials();
// Here save the $credentials array for later use
```


Then, when you want to reuse these credentials, you can rebuild a $request_object from them:

```php
$request_object = $oauth->auth('the_provider', array(
    'credentials' => $credentials
));
```

**Refreshing the token**

If a refresh token is available and the access token is expired, the `auth` method will automatically use that refresh token to get a new access token.

You can force the renewal by passing the `force_refresh` field in the options array:

```php
$request_object = $oauth->auth('the_provider', array(
    'credentials' => $credentials,
    'force_refresh' => true
));
```

You can also directly refresh a credentials array like this:

```php
$refreshed_credentials = $oauth->refreshCredentials($old_credentials);
```

Contributing to this SDK
------------------------

**Issues**

Please discuss issues and features on Github Issues. We'll be happy to answer to your questions and improve the SDK based on your feedback.

**Pull requests**

You are welcome to fork and make pull requests. We appreciate the time you spend working on this project and we will be happy to review your code and merge it if it brings nice improvements :)

If you want to do a pull request, please mind these simple rules :

- *One feature per pull request*
- *Write lear commit messages*
- *Unit test your feature* : if it's a bug fix for example, write a test that proves the bug exists and that your fix resolves it.
- *Write a clear description of the pull request*

If you do so, we'll be able to merge your pull request more quickly :)

The SDK is written as a Composer module. You can install its dependencies like this :

```sh
sdk/folder$ composer install
```

Testing the SDK
---------------

We use PHPUnit to test the SDK. To test it, just run the following from the SDK root folder :

```bash
$ ./vendor/phpunit/phpunit/phpunit
```

License
-------

The SDK is released under the Apache2 license.




[1]: https://github.com/oauth-io/oauth-js
[2]: https://github.com/oauth-io/oauth-phonegap
[3]: https://github.com/oauth-io/oauth-ios
[4]: https://github.com/oauth-io/oauth-android
