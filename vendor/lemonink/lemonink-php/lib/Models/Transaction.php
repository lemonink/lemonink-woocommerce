<?php

namespace LemonInk\Models;

class Transaction extends Base
{
  const DOWNLOADS_ENDPOINT = "https://dl.lemonink.co/transactions";

  protected $attributeNames = ["masterId", "watermarkValue", "watermarkParams", "token", "status", "downloadUrl"];

  protected $masterId;
  protected $watermarkValue;
  protected $watermarkParams;
  protected $token;
  protected $status;
  protected $downloadUrl;

  public function setMasterId($masterId)
  {
    $this->masterId = $masterId;
  }

  public function getMasterId()
  {
    return $this->masterId;
  }

  public function setWatermarkValue($watermarkValue)
  {
    $this->watermarkValue = $watermarkValue;
  }

  public function getWatermarkValue()
  {
    return $this->watermarkValue;
  }

  public function setWatermarkParams($watermarkParams)
  {
    $this->watermarkParams = $watermarkParams;
  }

  public function getWatermarkParams()
  {
    return $this->watermarkParams;
  }

  public function getToken()
  {
    return $this->token;
  }

  public function setToken($token)
  {
    $this->token = $token;
  }

  public function getStatus()
  {
    return $this->status;
  }

  protected function setStatus($status)
  {
    $this->status = $status;
  }

  protected function getDownloadUrl()
  {
    return $this->downloadUrl;
  }

  protected function setDownloadUrl($downloadUrl)
  {
    $this->downloadUrl = $downloadUrl;
  }

  public function getUrl($format = null)
  {
    return join(".", [$this->getDownloadUrl(), $format]);
  }
};
