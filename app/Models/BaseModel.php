<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Ramsey\Uuid\Uuid;

class BaseModel extends Model
{
  use SoftDeletes;
  use HasFactory;

  public $incrementing = false;
  protected $keyType = 'string';
  
  public const updateModifiedUser = true;

  public const ID = 'id';
  public const CREATED_AT = 'created_at';
  public const UPDATED_AT = 'updated_at';
  public const CREATED_BY = 'created_by';
  public const UPDATED_BY = 'updated_by';
  public const DELETED_AT = 'deleted_at';

  public const DATE_FORMAT_ISO8601 = 'c';
  public const DATE_FORMAT_DATETIME = 'Y-m-d H:i:s';
  public const DATE_FORMAT = self::DATE_FORMAT_ISO8601;

  public static $rules = [
    self::ID => 'required|string|max:36',
    self::CREATED_AT => 'nullable',
    self::UPDATED_AT => 'nullable',
    self::UPDATED_BY => 'nullable',
    self::CREATED_BY => 'nullable',
    self::DELETED_AT => 'nullable',
  ];

  public $fillable = [
    self::ID,
  ];

  protected $casts = [
    self::ID => 'string',
  ];

  protected $hidden = [
    self::CREATED_BY,
    self::UPDATED_BY,
    self::DELETED_AT,
  ];

  protected $dates = [
    self::CREATED_AT,
    self::UPDATED_AT
  ];
  
  protected static function boot()
  {
      parent::boot();
      static::creating(function ($model) {
        $model->beforeCreate();
      });
      static::updating(function ($model) {
        $model->beforeUpdate();
      });
      static::saving(function ($model) {
        $model->beforeUpdate();
      });
  }

  public static function findByKey($key)
  {
      $query = static::queryByKey($key);
      $data = $query->get()->first();
      return $data ? $data : null;
  }

  public static function queryByKey($key, $criteria = null)
  {
      $model = new static();
      $field = $model->getRouteKeyName();
      $query = $model->newQuery();
      if (is_array($key)) {
          $query = $query->whereIn($field, $key);
      } else {
          $query = $query->where($field, $key);
      }
      if ($criteria) {
          $query = $query->where($criteria);
      }
      return $query;
  }

  public static function count($search = [])
  {
    $model = new static();
    $query = $model->search($search);
    return $query->count();
  }

  public static function all(
    $search = [],
    $skip = null,
    $limit = null,
    $columns = ['*'],
    $sort_trg = array('modified_at', 'DESC')
  ) {
    $model = new static();
    $query = $model->search($search, $skip, $limit)
        ->orderBy($sort_trg[0], $sort_trg[1]);
    return $query->get($columns);
  }

  public function search($criteria = [])
  {
    $model = new static();
    $query = $model->newQuery();
    $attributes = \Schema::getColumnListing($model->getTable());

    if (count($criteria)) {
      foreach ($criteria as $key => $value) {
        if (in_array($key, $attributes)) {
          if (is_array($value)) {
            $query->whereIn($key, $value);
          } else {
            $query->where($key, $value);
          }
        }
      }
    }
    if (!empty($criteria['since'])) {
      $query->where('created_at', '>=', date('Y-m-d', strtotime($criteria['since'])));
    }
    if (!empty($criteria['until'])) {
      $query->where('created_at', '<=', date('Y-m-d', strtotime($criteria['until'])));
    }

    $sort = (
      !empty($criteria['sort']) &&
      \Schema::hasColumn($model->getTable(), $criteria['sort'])
    ) ? $criteria['sort'] : self::CREATED_AT;
    $order = (
      !empty($criteria['order']) &&
      (strtolower($criteria['order']) === 'asc' || strtolower($criteria['order']) === 'desc')
    ) ? $criteria['order'] : 'DESC';
    return $query->orderBy($sort, $order);
  }

  protected function beforeCreate()
  {
    if (empty($this->{$this->getKeyName()})) $this->{$this->getKeyName()} = (string) Uuid::uuid7();
    if (!static::updateModifiedUser) return;
    if (!is_object(\Auth::user())) return;
    $this->{static::CREATED_BY} = \Auth::user()->id;
    $this->{static::UPDATED_BY} = \Auth::user()->id;
  }

  protected function beforeUpdate()
  {
    if (!static::updateModifiedUser) return;
    if (!is_object(\Auth::user())) return;
    $this->{static::UPDATED_BY} = \Auth::user()->id;
  }

}
