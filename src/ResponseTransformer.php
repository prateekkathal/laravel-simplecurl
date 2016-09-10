<?php

namespace PrateekKathal\SimpleCurl;

use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ResponseTransformer {

  protected $response = [];

  /**
   * Set the response for this class from the CURL Request
   *
   * @param array $response
   */
  public function setResponse($response) {
    $this->response = $response;
    return $this;
  }

  /**
   * Transform to JSON
   *
   * @return JSON
   */
  public function toJson() {
    if(is_string($this->response['result'])) {
      return json_decode($this->response['result']);
    }
    return FALSE;
  }

  /**
   * Transfrom to Array
   *
   * @return array
   */
  public function toArray() {
    if(is_string($this->response['result'])) {
      return json_decode($this->response['result'], TRUE);
    }
    return FALSE;
  }

  /**
   * Transfrom to Collection
   *
   * @return Collection
   */
  public function toCollection() {
    $response = $this->toArray();
    return ($response) ? ($this->checkIfAllAreArray($response)) ? collect($response) : collect([$response]) : FALSE;
  }

  /**
   * Transfrom to Collection
   *
   * @return Collection
   */
  public function toPaginated($perPage) {
    $response = $this->toJson();

    if(!isset($response->total, $response->per_page, $response->current_page, $response->data))
      throw new \Exception('Missing Required Fields for Pagination');

    return new LengthAwarePaginator(
      collect($response->data), $response->total, $response->per_page,
      Paginator::resolveCurrentPage(), ['path' => Paginator::resolveCurrentPath()]
    );
  }

  /**
   * Check If All Elements in Response Are Array
   *
   * @param  array $response
   *
   * @return boolean
   */
  private function checkIfAllAreArray(array $response) {
    foreach ($response as $key => $value) {
      if(!is_array($value) || is_string($value)) {
        return false;
        break;
      }
    }
  }

  /**
   * Transform to a Specific Model
   *
   * @param  string $modelName
   * @param  array $nonFillableKeys
   * @param  array $relations
   *
   * @return Model
   */
  public function toModel($modelName, $nonFillableKeys = [], $relations = []) {
    $response = $this->toJson();
    return ($response) ? $this->toModelWithRelations($modelName, $response, $nonFillableKeys, $relations) : FALSE;
  }

  /**
   * Check if relations were passed as param and return response accordingly
   *
   * @param  string $modelName
   * @param  JSON $response
   * @param  array $nonFillableKeys
   * @param  array $relations
   *
   * @return Model
   */
  private function toModelWithRelations($modelName, $response, $nonFillableKeys = [], $relations = []) {
    $model = $this->responseToModel($modelName, $response, $nonFillableKeys, $relations);
    if(count($relations) > 0) {
      $model = $this->responseToModelRelation($relations, $response, $model);
    }
    return $model;
  }

  /**
   * Transform Response to the modelName passed as param
   *
   * @param  string $modelName
   * @param  JSON $response
   *
   * @return Model
   */
  private function responseToModel($modelName, $response, $nonFillableKeys = [], $relations = []) {
    $this->checkIfModelExists($modelName);
    $model = new $modelName;
    $fillableElements = $model->getFillable();

    if(is_array($response)) {
      return $this->responseArrayToModelCollection($modelName, $response, $nonFillableKeys, $relations);
    }

    $modelKeys = array_filter($fillableElements, function($fillable) use ($response) {
      foreach ($response as $key => $value) {
        if($key == $fillable || $key == 'created_at' || $key == 'updated_at')
          return $key;
      }
    });

    $modelKeys = array_merge($modelKeys, $nonFillableKeys);

    if(count($modelKeys) > 0) {
      foreach($modelKeys as $modelKey) {
        $value = isset($response->$modelKey) ? $response->$modelKey : null;
        $model->setAttribute($modelKey, $value);
      }
    }

    return $model;
  }

  /**
   * Transform Response to Collection of modelName passed as param
   *
   * @param  string $modelName
   * @param  JSON $response
   *
   * @return Collection
   */
  private function responseArrayToModelCollection($modelName, $response, $nonFillableKeys = [], $relations = []) {
    $this->checkIfModelExists($modelName);
    $model = new $modelName;
    $fillableElements = $model->getFillable();

    $modelKeys = array_filter($fillableElements, function($fillable) use ($response) {
      foreach ($response as $values) {
        foreach ($values as $key => $modelValue) {
          if($key == $fillable || $key == 'created_at' || $key == 'updated_at')
            return $key;
        }
      }
    });

    $modelKeys = array_merge($modelKeys, $nonFillableKeys);

    $newArray = [];
    if(count($modelKeys) > 0) {
      foreach($response as $key => $values) {
        $model = new $modelName;
        foreach($modelKeys as $modelKey) {
          $value = isset($values->$modelKey) ? $values->$modelKey : null;
          $model->setAttribute($modelKey, $value);
        }
        $newArray[] = $model;
      }
    }
    return collect($newArray);
  }

  /**
   * Set Relations to Parent Model
   *
   * @param array $relation
   * @param JSON $response
   * @param Model $model
   *
   * @return Model
   */
  private function responseToModelRelation($relations, $response, $model) {
    foreach($relations as $key => $relation) {
      $model = $this->setRelations($relation, $response, $model);
    }
    return $model;
  }

  /**
   * Set Recurring Relations to Model
   *
   * @param array $relation
   * @param JSON $response
   * @param Model $model
   *
   * @return Model
   */
  private function setRelations($relation, $response, $model) {
    $modelKey = array_keys($relation)[0];

    if(!isset($response->$modelKey)) {
      return $model;
    }

    if(count($relation) == 1) {
      return $model->setRelation($modelKey, $this->responseToModel(reset($relation), $response->$modelKey));
    }

    $currentRelations = array_keys($model->getRelations());

    if(count($currentRelations) > 0) {
      $relationalModels = array_filter($currentRelations, function($currentRelation) use ($response, $modelKey) {
        foreach($response as $key => $value) {
          if($key == $modelKey)
            return $modelKey;
        }
      });
      $newModel = $model->$modelKey;
    } else {
      $newModel = $this->responseToModel($relation[$modelKey], $response->$modelKey);
    }

    $newRelation = $relation;

    unset($newRelation[$modelKey]);

    return $model->setRelation($modelKey, $this->setRelations($newRelation, $response->$modelKey, $newModel));
  }

  /**
   * Check if Model Exists
   *
   * @param  string $modelName
   *
   * @return boolean
   */
  private function checkIfModelExists($modelName) {
    if(!class_exists($modelName)) {
      throw new ModelNotFoundException('Class' .$modelName. ' not found');
    }
    return TRUE;
  }

}
