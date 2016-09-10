# Laravel SimpleCurl
A Laravel package for handling simple CURL requests

For making simple GET/POST/PUT/DELETE requests.

## Without Any Specific Parameters
```
<?php

use SimpleCurl;

class UsersApiRepo {

  function allUsers() {
    // Gives Response As Array
    $usersArray = SimpleCurl::get('http://mysite.com/api/v1/users/all')->getResponseAsArray();

    // Gives Response As Json
    $usersJson = SimpleCurl::get('http://mysite.com/api/v1/users/all')->getResponseAsJson();

    // Gives Response As Collection
    $usersCollection = SimpleCurl::get('http://mysite.com/api/v1/users/all')->getResponseAsCollection();

    // Gives Response As LengthAwarePaginator (if the response is paginated)
    $usersPaginated = $simpleCurl->get('api/v1/users/all')->getPaginatedResponse();
  }

}
```

## With Specific Parameters
```
<?php

use SimpleCurl;

class UsersApiRepo {

  /*
   * A Config Variable which you can use to handle multiple CURL requests...
   */
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
    // Set Defaults for making a CURL Request
    $simpleCurl = SimpleCurl::setConfig($this->simpleCurlConfig);

    // Gives Response As Array
    $usersArray = $simpleCurl->get('api/v1/users/all')->getResponseAsArray();

    and so on...
  }

}
```

You may also use this function just for making things more **Laravel-like...**

```
function getUser() {
  /*
   * Please ensure only a single Model is present in the response for this. Multiple rows will not be
   * automatically get converted into Collections And Models atm.
   */

  /*
   * Keys set as fillable in that particular model are used here. Any fillable key, not present in the
   * response will be set as null and an instance of the Model will be returned.
   */
  $userModel = SimpleCurl::get('http://mysite.com/api/v1/user/1/get/')->getResponseAsModel('App\User')

  /*
   * There is also a second parameter which you can use to add keys which are present in response but
   * not in fillable.
   */
  $userModelWithPhoto = SimpleCurl::get('http://mysite.com/api/v1/user/1/get/')->getResponseAsModel('App\User', ['photo'])

  /*
   * There is a third parameter which you can use to add something from the response as a relation to it
   *
   * You will have to save a copy of the model so that SimpleCurl can get fillable fields from that class
   * and use for relational Models as well.
   */
  $relations = [
    [
      'photo' => 'App\Api\Photo'
    ],
    [
      'city'=> 'App\Api\City',
      'state' => 'App\Api\State'
    ]
  ];
  $userModelWithPhotoAsRelation = SimpleCurl::get('http://mysite.com/api/v1/user/1/get/')->getResponseAsModel('App\User', [], $relations);
}
```

Please note that `getResponseAsModel()` is experimental and may not run for many cases if the responses are altered a lot before they are sent.

You're most welcome! :smile: :sunglasses: :+1:
