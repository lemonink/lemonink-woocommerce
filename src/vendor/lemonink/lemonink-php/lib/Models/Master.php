<?php

namespace LemonInk\Models;

class Master extends Base
{
  protected $attributeNames = ["name", "formats"];

  protected $name;
  protected $formats;

  public function getName()
  {
    return $this->name;
  }

  public function setName($name)
  {
    $this->name = $name;
  }

  public function getFormats()
  {
    return $this->formats;
  }

  public function setFormats($formats)
  {
    $this->formats = $formats;
  }
};
