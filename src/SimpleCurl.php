<?php

namespace PrateekKathal\SimpleCurl;

use PrateekKathal\SimpleCurl\ResponseTransformer;

class SimpleCurl {

  /**
   * Config Variable
   *
   * @var array
   */
  protected $config;

  /**
   * CURL Response
   *
   * @var array
   */
  protected $response;

  /**
   * URL of CURL request
   *
   * @var string
   */
  protected $url;

  /**
   * Headers sent in CURL Request
   *
   * @var array
   */
  protected $headers;

  /**
   * SimpleCurl Constructor
   *
   * @param string $app
   */
  public function __construct() {
    $this->config = [
      'connectTimeout' => 10,
      'dataTimeout' => 30,
      'userAgent' => "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)",
      'baseUrl' => '',
      'defaultHeaders' => [],
    ];
    $this->response = $this->url = '';
    $this->headers = [];
    $this->responseTransformer = new ResponseTransformer;
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
  public function post($url, $data = [], $headers = [], $file = false) {
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
      return $this->response = ['status' => 'error', 'message' => 'Headers must be in the form of an array', 'result' => []];
    }
  }

  /**
   * Set Config Variables
   *
   * @param  array $config
   *
   * @return SimpleCurl
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
    return $this;
  }

  /**
   * Reset Config Variables
   *
   * @return SimpleCurl
   */
  public function resetConfig() {
    $this->config = [
      'connectTimeout' => 10,
      'dataTimeout' => 30,
      'userAgent' => "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)",
      'baseUrl' => '',
      'defaultHeaders' => [],
    ];
    return $this;
  }

  /**
   * Set Base URL
   *
   * @param  string $url
   *
   * @return SimpleCurl
   */
  public function setBaseUrl($url) {
    $this->config['baseUrl'] = $url;
    return $this;
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
   * Get Response HTTP Code
   *
   * @return int
   */
  public function getResponseCode() {
    return $this->response['http_code'];
  }

  /**
   * Get Response Content Type
   *
   * @return sting
   */
  public function getResponseContentType() {
    return $this->response['content_type'];
  }

  /**
   * Get Request Size
   *
   * @return sting
   */
  public function getRequestSize() {
    return $this->response['request_size'];
  }

  /**
   * Get Request URL
   *
   * @return string
   */
  public function getRequestUrl() {
    return $this->response['url'];
  }

  /**
   * Get Curl Error
   *
   * @return string
   */
  public function getCurlError() {
    return $this->response['curl_error'];
  }

  /**
   * Get Redirect Count
   *
   * @return int
   */
  public function getRedirectCount() {
    return $this->response['redirect_count'];
  }

  /**
   * Get Last Effective URL
   *
   * @return string
   */
  public function getEffectiveUrl() {
    return $this->response['effective_url'];
  }

  /**
   * Get Time Taken for the CURL Request
   *
   * @return float
   */
  public function getTotalTime() {
    return $this->response['total_time'];
  }

  /**
   * GET Response as JSON
   *
   * @return JSON
   */
  public function getResponseAsJson() {
    return $this->responseTransformer->setResponse($this->response)->toJson();
  }

  /**
   * Get Response as Array
   *
   * @return array
   */
  public function getResponseAsArray() {
    return $this->responseTransformer->setResponse($this->response)->toArray();
  }

  /**
   * Get Response as Collection
   *
   * @return Collection
   */
  public function getResponseAsCollection() {
    return $this->responseTransformer->setResponse($this->response)->toCollection();
  }

  /**
   * Get Response as Model
   *
   * @param  string $modelName
   *
   * @return Model
   */
  public function getResponseAsModel($modelName, $nonFillableKeys = [], $relations = []) {
    return $this->responseTransformer->setResponse($this->response)->toModel($modelName, $nonFillableKeys, $relations);
  }

  /**
   * Get Response as Model
   *
   * @param  string $modelName
   *
   * @return LengthAwarePaginator
   */
  public function getPaginatedResponse($perPage = 10) {
    return $this->responseTransformer->setResponse($this->response)->toPaginated($perPage);
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
  private function request($type, $url, $data = '', $headers = [], $file = false) {
    try {
    	$ch = curl_init();
    	curl_setopt ($ch, CURLOPT_URL, $url);
    	curl_setopt ($ch, CURLOPT_USERAGENT, $this->config['userAgent']);
      if(!empty($headers)) {
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
      }
      if($type == 'POST' && !empty($data)) {
        curl_setopt ($ch, CURLOPT_POST, 1);
        if((!empty($file) || in_array('Content-Type: application/json', $this->headers)) {
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
      $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
      $redirectCount = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);
      $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
      $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
      $curlError = curl_error($ch);
      curl_close ($ch);

      $this->response = [
        'http_code' => $httpCode, 'request_size' => $requestSize, 'curl_error' => $curlError,
        'url' => $url, 'content_type' => $contentType, 'redirect_count' => $redirectCount,
        'effective_url' => $effectiveUrl, 'total_time' => $totalTime, 'result' => $result
      ];
    	if ($httpCode == "200") {
        $this->response['status'] = 'success';
      } else {
        $this->response['status'] = 'failed';
      }
      return $this;
    } catch (Exception $e) {
      $this->response = [
        'http_code' => null, 'request_size' => null, 'curl_error' => null,
        'url' => $url, 'content_type' => null, 'redirect_count' => null,
        'effective_url' => null, 'total_time' => null, 'result' => $e
      ];
      return $this;
    }
  }

}
