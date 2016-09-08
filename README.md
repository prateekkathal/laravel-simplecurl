# Laravel SimpleCurl
A Laravel package for handling simple CURL requests

For making simple GET/POST/PUT/DELETE requests.

## Without Any Specific Parameters
```
<?php

use SimpleCurl;

class UsersApiRepo {

  function __construct() {
    $simpleCurl = new SimpleCurl;
  }

  function getUsers() {
    $url = 'http://mysite.com/api/v1/users/all'
    $this->simpleCurl->get($url);
    $responseAsArray = $this->simpleCurl->getResponseAsArray();
    $responseAsJson = $this->simpleCurl->getResponseAsJson();
  }

}
```
## With Specific Parameters
```
<?php

use SimpleCurl;

class UsersApiRepo {

  function __construct() {
    $simpleCurl = new SimpleCurl;
    $config = [
      'connectTimeout' => 30,
      'dataTimeout' => 60,
      'baseUrl' => 'http://mysite.com/',
      'defaultHeaders' => [
        'Authorization: Bearer {bearer_token}',
        'Content-Type: application/json'
      ],
    ]
    $simpleCurl->setConfig($config)
  }

  function getUsers() {
    $this->simpleCurl->get('api/v1/users/all')
    $responseAsArray = $this->simpleCurl->getResponseAsArray();
    $responseAsJson = $this->simpleCurl->getResponseAsJson();
  }

}
```
