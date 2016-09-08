<?php

namespace PrateekKathal\SimpleCurl;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class SimpleCurl {

  protected $config, $response, $url, $headers;

  /**
   * SimpleCurl Constructor
   *
   * @param string $app
   */
  public function __construct($app = '') {
    if(!empty($app)) {
      $this->app = $app;
    }
    $this->config = [
      'connectTimeout' => 10,
      'dataTimeout' => 30,
      'userAgent' => "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)",
      'baseUrl' => '',
      'defaultHeaders' => [],
    ];
    $this->response = $this->url = '';
    $this->headers = [];
  }

  /**
   * Curl GET Request
   *
   * @param  string $url
   * @param  array $data
   * @param  array $headers
   *
   * @return array
   */
  public function get($url, $data = [], $headers = []) {
    return $this->request('GET', $this->getFullUrl($url), $data, $this->getAllHeaders($headers));
  }

  /**
   * Curl POST Request
   *
   * @param  string $url
   * @param  array $data
   * @param  array $headers
   * @param  string $file
   *
   * @return array
   */
  public function post($url, $data = [], $headers = [], $file = '') {
    return $this->request('POST', $this->getFullUrl($url), $data, $this->getAllHeaders($headers), $file);
  }

  /**
   * Curl PUT Request
   *
   * @param  string $url
   * @param  array $data
   * @param  array $headers
   *
   * @return array
   */
  public function put($url, $data = [], $headers = []) {
    return $this->request('PUT', $this->getFullUrl($url), $data, $this->getAllHeaders($headers));
  }

  /**
   * Curl DELETE Request
   *
   * @param  string $url
   * @param  array $data
   * @param  array $headers
   *
   * @return array
   */
  public function delete($url, $data = [], $headers = []) {
    return $this->request('DELETE', $this->getFullUrl($url), $data, $this->getAllHeaders($headers));
  }

  /**
   * Validate Inputs Before Sending CURL Request
   *
   * @param  string $url
   * @param  array $data
   * @param  array $headers
   *
   * @return array
   */
  private function validateInputs($url, $data = [], $headers = []) {
    if(empty($this->getFullUrl($url))) {
      return $this->response = ['status' => 'error', 'message' => 'No URL Given', 'result' => []];
    }
    if(!is_array($data)) {
      return $this->response = ['status' => 'error', 'message' => 'Data must be in the form of an array', 'result' => []];
    }
    if(!is_array($this->getAllHeaders($headers))) {
      return $this->response = ['status' => 'error', 'message' => 'Data must be in the form of an array', 'result' => []];
    }
  }

  /**
   * Set Config Variables
   *
   * @param  array $config
   *
   * @return array
   */
  public function setConfig($config = []) {
    foreach($config as $key => $value) {
      switch($key) {
        case 'connectTimeout':
          $this->config['connectTimeout'] = $config['connectTimeout'];
          break;
        case 'dataTimeout':
          $this->config['dataTimeout'] = $config['dataTimeout'];
          break;
        case 'userAgent':
          $this->config['userAgent'] = $config['userAgent'];
          break;
        case 'baseUrl':
          $this->config['baseUrl'] = $config['baseUrl'];
          break;
        case 'defaultHeaders':
          $this->config['defaultHeaders'] = $config['defaultHeaders'];
          break;
        default: break;
      }
    }
  }

  /**
   * Set Base URL
   *
   * @param  string $url
   *
   * @return string
   */
  public function setBaseUrl($url) {
    return $this->config['baseUrl'] = $url;
  }

  /**
   * Get Full URL
   *
   * @param  string $url
   *
   * @return string
   */
  private function getFullUrl($url) {
    return $this->url = $this->config['baseUrl'].$url;
  }

  /**
   * Get Default Headers
   *
   * @return array
   */
  private function getDefaultHeaders() {
    return $this->config['defaultHeaders'];
  }

  /**
   * Get All Headers
   *
   * @param  array $headers
   *
   * @return array
   */
  public function getAllHeaders($headers) {
    return $this->headers = array_merge($this->config['defaultHeaders'], $this->headers);
  }

  /**
   * Get Response
   *
   * @return array
   */
  public function getResponse() {
    return $this->response;
  }

  /**
   * GET Response as JSON
   *
   * @return JSON
   */
  public function getResponseAsJson() {
    if(is_string($this->response['result'])) {
      return json_decode($this->response['result']);
    }
    return FALSE;
  }

  /**
   * Get Response as Array
   *
   * @return array
   */
  public function getResponseAsArray() {
    if(is_string($this->response['result'])) {
      return json_decode($this->response['result'], TRUE);
    }
    return FALSE;
  }

  /**
   * Get Response as Collection
   *
   * @return Collection
   */
  public function getResponseAsCollection() {
    $response = $this->getResponseAsArray();
    return ($response) ? collect($response) : FALSE;
  }

  /**
   * Get Response as Model
   *
   * @param  string $modelName
   *
   * @return Model
   */
  public function getResponseAsModel($modelName, $nonFillableKeys = []) {
    $response = $this->getResponseAsJson();
    return ($response) ? $this->transformResponseToModel($modelName, $response, $nonFillableKeys) : FALSE;
  }

  /**
   * Transform Curl Response to its Corresponding Model
   *
   * @param  string $modelName
   * @param  JSON $response
   *
   * @return Model
   */
  private function transformResponseToModel($modelName, $response, $nonFillableKeys = []) {
    $this->checkIfModelExists($modelName);
    $model = new $modelName;
    $fillableElements = $model->getFillable();

    $modelKeys = array_filter($fillableElements, function($fillable) use ($response) {
      foreach ($response as $key => $value) {
        if($key == $fillable || $key == 'created_at' || $key == 'updated_at')
          return $key;
      }
    });

    $modelKeys = array_merge($modelKeys, $nonFillableKeys);

    if(count($modelKeys) > 0) {
      foreach($modelKeys as $modelKey) {
        $value = isset($response->$modelKey) ? $response->$modelKey : null;
        $model->setAttribute($modelKey, $value);
      }
    }

    return $model;
  }

  /**
   * Check if Model Exists
   *
   * @param  string $modelName
   *
   * @return boolean
   */
  private function checkIfModelExists($modelName) {
    if(!class_exists($modelName)) {
      throw new ModelNotFoundException($modelName. ' not found!');
    }
    return TRUE;
  }

  /**
   * CURL Request
   *
   * @param  string $type
   * @param  string $url
   * @param  string $data
   * @param  array $headers
   * @param  string $file
   *
   * @return array
   */
  private function request($type, $url, $data = '', $headers = [], $file = '') {
    try {
    	$ch = curl_init();
    	curl_setopt ($ch, CURLOPT_URL, $url);
    	curl_setopt ($ch, CURLOPT_USERAGENT, $this->config['userAgent']);
      if(!empty($headers)) {
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
      }
      if($type == 'POST' && !empty($data)) {
        curl_setopt ($ch, CURLOPT_POST, 1);
        if((!empty($file) && !empty($data[$file])) || in_array('Content-Type: application/json', $this->headers)) {
          curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);
        } else {
          curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
      }
      if($type == 'PUT' || $type == 'DELETE') {
        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($data));
      }
      curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $this->config['connectTimeout']);
    	curl_setopt ($ch, CURLOPT_TIMEOUT, $this->config['dataTimeout']);
    	$result = curl_exec ($ch);
    	$httpCode = curl_getinfo ($ch, CURLINFO_HTTP_CODE);
      $requestSize = curl_getinfo ($ch, CURLINFO_REQUEST_SIZE);
      $curlError = curl_error($ch);
      curl_close ($ch);

    	if ($httpCode == "200") {
        $this->response = ['status' => 'success', 'result' => $result];
    	}
      $this->response = ['status' => 'failed', 'result' => $result, 'error_code' => $httpCode, 'request_size' => $requestSize, 'curl_error' => $curlError];
      return $this;
    } catch (Exception $e) {
      $this->response = ['status' => 'error', 'result' => $e];
      return $this;
    }
  }

}
