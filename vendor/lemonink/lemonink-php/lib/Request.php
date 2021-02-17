<?php

namespace LemonInk;

class Request
{
  protected $method;
  protected $url;
  protected $options;

  public function __construct($method, $url = '', array $options = [])
  {
    $this->method = $method;
    $this->url = $url;
    $this->options = $options;
  }

  public function make()
  {
    $curlHandle = curl_init();

    curl_setopt($curlHandle, CURLOPT_URL, $this->url);
    curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, TRUE);
    
    if ($this->method == "POST") {
      curl_setopt($curlHandle, CURLOPT_POST, 1);
    }

    if ($this->method == "PATCH") {
      curl_setopt($curlHandle, CURLOPT_PATCH, 1);
    }

    $headers = $this->getHeaders();
    if (!empty($headers)) {
      curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);
    }

    $body = $this->getBody();
    if (!empty($body)) {
      curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $body);
    }

    $rawResponse = curl_exec($curlHandle);
    $response = new Response($rawResponse, $curlHandle);
    curl_close($curlHandle);
    return $response;
  }

  protected function getHeaders()
  {
    if (!empty($this->options["headers"])) {
      $headers = [];
      foreach ($this->options["headers"] as $key => $value) {
        $headers[] = $key . ": " . $value;
      }
      return $headers;
    }
  }

  protected function getBody()
  {
    if (!empty($this->options["body"])) {
      return $this->options["body"];
    }
  }
}