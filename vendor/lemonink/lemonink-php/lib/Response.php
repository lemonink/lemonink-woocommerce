<?php

namespace LemonInk;

class Response
{
  protected $body;
  
  protected $statusCode;

  public function __construct($body, $curlHandle)
  {
    $this->body = $body;
    $this->statusCode = (int)curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
  }

  public function getStatusCode()
  {
    return $this->statusCode;
  }

  public function getBody()
  {
    return $this->body;
  }

  protected function getHeaderSize()
  {
    return $this->headerSize;
  }
}