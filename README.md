# Laravel SimpleCurl
A Laravel package for handling simple CURL requests... **the Laravel way...**

## For installation,

* In terminal, paste this
```php
composer require prateekkathal/laravel-simplecurl 0.*
```

* Open **app.php** and add this in the **'providers'** array
```php
PrateekKathal\SimpleCurl\SimpleCurlServiceProvider::class,
```

* Then add this to the **'aliases'** array
```php
'SimpleCurl' => PrateekKathal\SimpleCurl\SimpleCurlFacade::class,
```

## Request Functions

|         Function Name          |     Return Type    |                                 Example                                   |
|             ---                |       ---          |                                   ---                                     |
|            get()               |     SimpleCurl     |            SimpleCurl::get($url = '', $data = [], $headers = [])          |
|           post()               |     SimpleCurl     |   SimpleCurl::post($url = '', $data = [], $headers = [], $file = false)   |
|            put()               |     SimpleCurl     |           SimpleCurl::put($url = '', $data = [], $headers = [])           |
|          delete()              |     SimpleCurl     |         SimpleCurl::delete($url = '', $data = [], $headers = [])          |

## Response Functions
|         Function Name          |     Return Type    |                                 Example                                   |
|             ---                |        ---         |                                   ---                                     |
|        getResponse()           |        array       |                 ['http_code' => 200, 'result' => ...]                     |
|        getResponseCode()       |        array       |                                   200                                     |
|        getRequestUrl()         |       string       |                      'http://mysite.com/api/v1/....'                      |
|        getRequestSize()        |        int         |                                   300                                     |
|        getTotalTime()          |        int         |                                   0.2                                     |
|    getResponseContentType()    |       string       |                             'application/json'                            |
|       getRedirectCount()       |        int         |                                    0                                      |
|       getEffectiveUrl()        |       string       |                       'http://mysite.com/api/v1/....'                     |
|         getCurlError()         |       string       |                       'URL is not properly formatted'                     |
|     getResponseAsArray()       |       array        |                 ['id' => 1, 'name' => Prateek Kathal ...]                 |
|     getResponseAsJson()        |       json         |                 {"id": 1, "name": "Prateek Kathal" ...}                   |
|   getResponseAsCollection()    |    Collection      |     Collection => { [ 0 => {"id": 1, "name": "Prateek Kathal" }... ] }    |
|   getPaginatedResponse()       |LengthAwarePaginator| LengthAwarePaginator => { 'total' => 10, per_page => 10, data => [ { "id": 1, "name": "Prateek Kathal" }... } ] |
|    getResponseAsModel()        |     Model          |      User => { "attributes" : { "id": 1, "name": "Prateek Kathal" } }     |

## Making simple **GET/POST/PUT/DELETE** requests,

**Without Config Variables**
```php
<?php

use SimpleCurl;

class UsersApiRepo
{

  function allUsers()
  {
    // Gives Response As Array
    $usersArray = SimpleCurl::get('http://mysite.com/api/v1/user/all')->getResponseAsArray();

    // Or (Gives Response As Json)
    $usersJson = SimpleCurl::get('http://mysite.com/api/v1/user/all')->getResponseAsJson();

    // Or (Gives Response As Collection)
    $usersCollection = SimpleCurl::get('http://mysite.com/api/v1/user/all')->getResponseAsCollection();

    // Or (Gives Response As LengthAwarePaginator, if the response is paginated)
    $usersPaginated = SimpleCurl::get('http://mysite.com/api/v1/user/all')->getPaginatedResponse();
  }

  function storeUser()
  {
    $url = 'http://mysite.com/api/v1/user/store';
    $inputs = [
      'name' => 'Prateek Kathal',
      'status' => 'Feeling positive!',
      'photo' => new \CURLFile($photoUrl, $mimeType, $photoName)
    ];
    $headers = ['Authorization: Bearer tokenForAuthorization'];
    $usersJson = SimpleCurl::post($url, $inputs, $headers, true)->getResponseAsJson();
  }

  function updateUser($id)
  {
    // Please note that CURL does not support posting Images/Files via PUT requests.
    $url = 'http://mysite.com/api/v1/user/' .$id. '/update';
    $headers = ['Authorization: Bearer tokenForAuthorization'];
    $inputs = [
      'status' => 'Feeling amazing!'
    ];
    $usersJson = SimpleCurl::put($url, $inputs, $headers)->getResponseAsJson();
  }

  function deleteUser($id)
  {
    // Please note that CURL does not support posting Images/Files via PUT requests.
    $url = 'http://mysite.com/api/v1/user/' .$id. '/delete';
    $headers = ['Authorization: Bearer tokenForAuthorization'];
    $usersJson = SimpleCurl::put($url, [], $headers)->getResponseAsJson();
  }

}
```

You may also use this function just for making things more **Laravel-like...**

**Add this trait to your Model (say Photo)
```php
use PrateekKathal\SimpleCurl\SimpleCurlTrait;
```

** Then add these 2 things in your model**
```php
<?php

class Photo extends Model
{
  use SimpleCurlTrait;

  protected $apiAttributes = ['id', 'user_id', 'name', 'mime_type'];
}
```

```php
function getUser($id)
{
  /*
   * Please ensure only a single Model is present in the response for this. Multiple rows will not be
   * automatically get converted into Collections And Models atm.
   *
   * Keys set as fillable in that particular model are used here. Any fillable key, not present in the
   * response will be set as null and an instance of the Model will be returned.
   */
  $userModel = SimpleCurl::get('http://mysite.com/api/v1/user/' .id. '/get/')->getResponseAsModel('App\User')

  /*
   * There is also a second parameter which you can use to add something from the response as a relation
   * to it.
   *
   * You will have to save a copy of the model somewhere so that SimpleCurl can get apiAttributes/fillable fields from
   * that class and use for relational Models as well.
   */
  $relations = [
    [
      'photo' => 'App\Photo'
    ],
    [
      'city'=> 'App\City',                  //This will work as city.state and give state as a relation to city
      'state' => 'App\State'
    ]
  ];
  $userModelWithPhotoAsRelation = SimpleCurl::get('http://mysite.com/api/v1/user/' .id. '/get/')->getResponseAsModel('App\User', $relations);
}
```

Please note that `getResponseAsModel()` is experimental and may not run for many cases if the responses are altered a lot before they are sent. For eg - When you convert an attribute `created_at` into a separate format using `$casts` variable.

Also, you can make a config file (say **config/relations.php**) and save all your relations in it and call separately.

**With Config Variables**
```php
<?php

use SimpleCurl;

class UsersApiRepo
{

  /*
   * A Config Variable which you can use to handle multiple CURL requests...
   */
  protected $simpleCurlConfig;

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

  function allUsers()
  {
    // Set Defaults for making a CURL Request
    $simpleCurl = SimpleCurl::setConfig($this->simpleCurlConfig);

    // or if you just want to set base url
    // $simpleCurl = SimpleCurl::setBaseUrl($this->simpleCurlConfig['baseUrl']);

    // you can also change the default UserAgent
    // $simpleCurl = SimpleCurl::setUserAgent("Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");

    // Gives Response As Array
    $usersArray = $simpleCurl->get('api/v1/users/all')->getResponseAsArray();

    and so on...
  }

  and so on.....

}
```

You are most welcome to create pull requests and post issues! :smile: :sunglasses: :+1:
