<?php

namespace LemonInk\Models;

class Base
{
  protected $attributeNames = [];
  protected $id;

  public function getId()
  {
    return $this->id;
  }

  public function setId($id)
  {
    $this->id = $id;
  }

  public function isPersisted()
  {
    return null !== $this->getId();
  }

  public function getModelName()
  {
    $name = get_class($this);
    $name = str_replace("LemonInk\\Models\\", "", $name);
    $name = lcfirst($name);

    return $name;
  }

  public function setAttributes($attributes)
  {
    foreach (array_merge(["id"], $this->attributeNames) as $attribute) {
      // Write-only attributes (e.g. a transaction's lcpParams) are not echoed back
      // by the API; keep the value that was sent.
      if (!array_key_exists($attribute, $attributes)) {
        continue;
      }
      $setter = "set" . ucfirst($attribute);
      $this->$setter($attributes[$attribute]);
    }
  }

  public function toArray()
  {
    $output = [];

    foreach ($this->attributeNames as $attribute) {
      $getter = "get" . ucfirst($attribute);
      $output[$attribute] = $this->$getter();
    }

    return $output;
  }
};
