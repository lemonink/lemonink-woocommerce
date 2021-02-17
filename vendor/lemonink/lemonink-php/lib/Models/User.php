<?php

namespace LemonInk\Models;

class User extends Base
{
  protected $attributeNames = ["watermarkParams"];

  protected $watermarkParams;
  
  public function setWatermarkParams($watermarkParams)
  {
    $this->watermarkParams = $watermarkParams;
  }

  public function getWatermarkParams()
  {
    return $this->watermarkParams;
  }
};
