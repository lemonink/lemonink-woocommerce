<?php

namespace LemonInk;

class Service
{
  protected $endpoint = "https://api.lemonink.co/v1/";

  protected $apiKey;

  public function __construct($apiKey)
  {
    $this->setApiKey($apiKey);
  }

  public function getEndpoint()
  {
    return $this->endpoint;
  }

  public function setApiKey($apiKey)
  {
    $this->apiKey = (string) $apiKey;
  }

  public function getApiKey()
  {
    return $this->apiKey;
  }

  public function getDefaultHeaders()
  {
    return [
      "Authorization" => "Token token=" . $this->getApiKey(),
      "Content-Type"  => "application/json",
      "User-Agent"    => "lemonink-php"
    ];
  }

  public function get($url, array $options = [])
  {
    return $this->makeRequest("GET", $url, $options);
  }

  public function post($url, array $options = [])
  {
    return $this->makeRequest("POST", $url, $options);
  }

  public function patch($url, array $options = [])
  {
    return $this->makeRequest("PATCH", $url, $options);
  }

  protected function makeRequest($method, $url = '', array $options = [])
  {
    $url = $this->getEndpoint() . $url;
    if (!isset($options["headers"])) {
      $options["headers"] = array();
    }
    $options["headers"] = array_merge($this->getDefaultHeaders(), $options["headers"]);

    $request = new Request($method, $url, $options);
    $response = $request->make();
    return $response;
  }
}
