# Laravel SimpleCurl
A Laravel package for handling simple CURL requests

For making simple GET/POST/PUT/DELETE requests.

## Without Any Specific Parameters
```
<?php

use SimpleCurl;

class UsersApiRepo {

  function allUsers() {
    $usersArray = SimpleCurl::get('http://mysite.com/api/v1/users/all')->getResponseAsArray();

    // or
    $usersJson = SimpleCurl::get('http://mysite.com/api/v1/users/all')->getResponseAsJson();

    // or
    $usersCollection = SimpleCurl::get('http://mysite.com/api/v1/users/all')->getResponseAsCollection();
  }

}
```

## With Specific Parameters
```
<?php

use SimpleCurl;

class UsersApiRepo {

  protected simpleCurlConfig;

  function __construct() {
    $this->simpleCurlConfig = [
      'connectTimeout' => 30,
      'dataTimeout' => 60,
      'baseUrl' => 'http://mysite.com/',
      'defaultHeaders' => [
        'Authorization: Bearer {bearer_token}',
        'Content-Type: application/json'
      ],
    ];
  }

  function allUsers() {
    $simpleCurl = SimpleCurl::setConfig($this->simpleCurlConfig);
    $usersArray = $simpleCurl->get('api/v1/users/all')->getResponseAsArray();

    // or
    $usersJson = $simpleCurl->get('api/v1/users/all')->getResponseAsJson();

    // or
    $usersCollection = $simpleCurl->get('api/v1/users/all')->getResponseAsCollection();
  }

}
```

You may also use this function just for making things more Laravel-like...

```
function getUser() {
  // Please ensure only a single Model is present in the response for this. Multiple rows will not be
  // automatically get converted into Collections And Models atm.

  // Keys set as fillable in that particular model are used here. Any fillable key, not present in the
  // response will be set as null and an instance of the Model will be returned.
  $userModel = SimpleCurl::get('http://mysite.com/api/v1/user/1/get/')->getResponseAsModel('App\User')

  // There is also a second parameter to getResponseAsModel() which you can use to add keys which are
  // present in response but not in fillable.
  $userModelWithPhoto = SimpleCurl::get('http://mysite.com/api/v1/user/1/get/')->getResponseAsModel('App\User', ['photo'])
}
```

I have not currently worked upon using this for Eloquent Relations, but if required, please create an issue and I will look into it. :)
