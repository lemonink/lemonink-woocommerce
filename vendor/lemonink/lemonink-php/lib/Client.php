<?php

namespace LemonInk;

function camelcase2underscore($string) {
  return strtolower(preg_replace(
        ["/([A-Z]+)/", "/_([A-Z]+)([A-Z][a-z])/"],
        ["_$1", "_$1_$2"],
        lcfirst($string)
    ));
}

function underscore2camelcase($string) {
  return lcfirst(implode(
    "",
    array_map(
      "ucfirst",
      explode("_", $string)
    )
  ));
}

class Client
{
  protected $apiKey;
  protected $service;

  public function __construct($apiKey)
  {
    $this->apiKey = $apiKey;
  }

  public function find($modelName, $id)
  {
    $uri = $this->getUriFor($modelName, $id);
    
    $response = $this->getService()->get($uri);

    if ($response->getStatusCode() === 200) {
      $data = $this->deserialize($modelName, $response->getBody());
      $modelClass = $this->getModelClassFor($modelName);

      $model = new $modelClass();
      $model->setAttributes($data);

      return $model;
    }
  }

  public function findAll($modelName)
  {
    $uri = $this->getUriFor($modelName);
    
    $response = $this->getService()->get($uri);

    if ($response->getStatusCode() === 200) {
      $data = $this->deserialize($modelName, $response->getBody());
      $modelClass = $this->getModelClassFor($modelName);

      $models = [];
      foreach ($data as $row) {
        $model = new $modelClass();
        $model->setAttributes($row);
        $models[] = $model;
      }

      return $models;
    } else {
      return [];
    }
  }

  public function save($model)
  {
    $uri = $this->getUriFor($model->getModelName(), $model->getId());
    $action = $model->isPersisted() ? "patch" : "post";

    $json = $this->serialize($model->getModelName(), $model->toArray());

    $response = $this->getService()->$action($uri, ["body" => $json]);

    if ($response->getStatusCode() < 300) {
      $model->setAttributes($this->deserialize($model->getModelName(), $response->getBody()));
    } else {
      $errors = $this->deserializeErrors($response->getBody());
      $error = $errors[0];
      throw new Exception($error["title"], intval($error["code"]));
    }
  }

  protected function getService()
  {
    if (!$this->service) {
      $this->service = new Service($this->apiKey);
    }

    return $this->service;
  }

  protected function serialize($modelName, $array)
  {
    $output = [];

    foreach ($array as $key => $value) {
      $output[camelcase2underscore($key)] = $value;
    }

    $output = [
      camelcase2underscore($modelName) => $output
    ];

    if (defined('JSON_INVALID_UTF8_IGNORE')) {
      return json_encode($output, JSON_INVALID_UTF8_IGNORE);
    } else {
      return json_encode($output);
    }
  }

  protected function deserialize($modelName, $data)
  {
    $singularKey = camelcase2underscore($modelName);
    $pluralKey = $singularKey . "s";

    $json = json_decode($data, true);

    if (array_key_exists($singularKey, $json)) {
      $json = $json[$singularKey];

      return $this->deserializeRow($json);
    } else if (array_key_exists($pluralKey, $json)) {
      $json = $json[$pluralKey];
      $rows = [];

      foreach ($json as $row) {
        $rows[] = $this->deserializeRow($row);
      }

      return $rows;
    }
  }

  protected function deserializeRow($row)
  {
    $attributes = [];

    foreach ($row as $key => $value) {
      $attributes[underscore2camelcase($key)] = $value;
    }

    return $attributes;
  }

  protected function deserializeErrors($data)
  {
    $json = json_decode($data, true);
    $json = $json["errors"];

    $errors = [];

    foreach ($json as $errorJson) {
      $error = [];
      foreach ($errorJson as $key => $value) {
        $error[underscore2camelcase($key)] = $value;
      }
      $errors[] = $error;
    }

    return $errors;
  }

  protected function getUriFor($modelName, $id = null)
  {
    // Crude pluralization. Sufficient for now.
    $parts = [$modelName . "s"];
    if ($id) {
      $parts[] = $id;
    }
    return implode("/", $parts);
  }

  protected function getModelClassFor($modelName)
  {
    $modelName = ucfirst($modelName);
    return "LemonInk\\Models\\$modelName";
  }
}
