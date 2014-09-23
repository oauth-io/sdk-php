OAuth.io PHP SDK
================

OAuth that just works !

This SDK allows you to use OAuth.io from a PHP backend, to handle the authentication and API calls from your server instead of from your front-end, for 100+ API providers.

The current version of the SDK is `0.3.0`. Older versions are deprecated.

You can also get nightlies by checking out our `develop` branch.

To get more information about this SDK and its method, please refer to its [reference documentation](https://oauth.io/docs/api-reference/server/php) on OAuth.io.

Features
--------

- Server-side OAuth authentication flow
- Requests to API from the backend
- Unified user information (`.me()` method) requests when available
- Access token renewal with the refresh_token when available

Common use-Case
---------------

You don't want to use APIs directly from the front-end, but rather through web-services inside your PHP backend.

Installation
------------

First of all, you'll need to set your app's backend to **PHP** in your OAuth.io [dashboard](https://oauth.io/dashboard).

This allows you to get a refresh token from the provider if available.

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

Using the SDK
-------------

The `OAuth` class is stored in the `OAuth_io` namespace. You need to include it in your file like this (make sure you have required the Composer autoloader file):

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

**Authenticating the user**

The first thing you need to do is to create an endpoint that will redirect your user to the provider's authentication page, so that the user can accept the permissions your app needs.

In this endpoint, call the `redirect` method like this:

```php
$oauth->redirect('the_provider', '/callback/url');
```

This will automatically redirect your user to the provider's website. Once he has accepted the permissions, he will be redirected to the '/callback/url' on your app, where you'll be able to retrieve a request object.

In an endpoint associated to the '/callback/url', call the `auth` method with the `redirect` option set to true to get a request object, like this:

```php
$request_object = $oauth->auth('the_provider', array(
    'redirect' => true
));
```

`$request_object` is an object that allows you to perform requests (see further down to learn how to), and that contains the user's credentials.

**Using the session to get a request object**

Usually, you'll want to make calls to the API several times while the user is connected to your app. Once you've authenticated the user once with a code, the session is automatically configured to work with the SDK.

Thus, you just need to do this to get a request object:

```php
$request_object = $oauth->auth('the_provider');
```

**Saving credentials to re-generate a request object**

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
$facebook_requester = $oauth->auth('facebook', array(
    'redirect' => true
));

$result = $facebook_requester->me(array('firstname', 'lastname', 'email'));

// you'll have $result["firstname"], $result["lastname"] and $result["email"] set with the user's facebook information.
//?>
```

You can refer to the OAuth.io me() feature to get more information about the fields that are returned by this method.

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
- *Write clear commit messages*
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
