<?php

namespace PrateekKathal\SimpleCurl;

class SimpleCurl {

  protected $config, $response, $url, $headers;

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

  public function get($url, $data = [], $headers = []) {
    return $this->request('GET', $this->getFullUrl($url), $data, $this->getAllHeaders($headers));
  }

  public function post($url, $data = [], $headers = [], $file = '') {
    return $this->request('POST', $this->getFullUrl($url), $data, $this->getAllHeaders($headers), $file);
  }

  public function put($url, $data = [], $headers = []) {
    return $this->request('PUT', $this->getFullUrl($url), $data, $this->getAllHeaders($headers));
  }

  public function delete($url, $data = [], $headers = []) {
    return $this->request('DELETE', $this->getFullUrl($url), $data, $this->getAllHeaders($headers));
  }

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

  public function setConfig($config = '') {
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

  public function setBaseUrl($url) {
    return $this->config['baseUrl'] = $url;
  }

  private function getFullUrl($url) {
    return $this->url = $this->config['baseUrl'].$url;
  }

  private function getDefaultHeaders() {
    return $this->config['defaultHeaders'];
  }

  public function getAllHeaders($headers) {
    return $this->headers = array_merge($this->config['defaultHeaders'], $this->headers);
  }

  public function getResponse() {
    return $this->response;
  }

  public function getResponseAsJson() {
    if(is_string($this->response['result'])) {
      return json_decode($this->response['result']);
    }
    return FALSE;
  }

  public function getResponseAsArray() {
    if(is_string($this->response['result'])) {
      return json_decode($this->response['result'], TRUE);
    }
    return FALSE;
  }

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
        return $this->response = ['status' => 'success', 'result' => $result];
    	}
      return $this->response = ['status' => 'failed', 'result' => $result, 'error_code' => $httpCode, 'request_size' => $requestSize, 'curl_error' => $curlError];
    } catch (Exception $e) {
      return $this->response = ['status' => 'error', 'result' => $e];
    }
  }

}
