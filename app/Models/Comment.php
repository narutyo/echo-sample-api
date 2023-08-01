<?php

namespace App\Models;

use App\Events\CommentPostedEvent;

class Comment extends BaseModel
{
  public const TABLE_NAME = 'comments';

  // リレーション
  public const IDEA = 'idea';

  // フィールド
  public const IDEA_ID = 'idea_id';
  public const BODY = 'body';

  public $fillable = [
    self::IDEA_ID,
    self::BODY,
  ];

  protected $casts = [
    self::BODY  => 'string',
    self::CREATED_AT => 'datetime:' . self::DATE_FORMAT,
    self::UPDATED_AT => 'datetime:' . self::DATE_FORMAT,
  ];

  protected $hidden = [
    self::IDEA_ID,
    self::UPDATED_BY,
    self::CREATED_BY,
    self::DELETED_AT,
  ];

  public function idea()
  {
    return $this->belongsTo('App\Models\Idea');
  }
  
  public static function add($input)
  {
    $model = self::create($input)->fresh();
    $idea = $model->{self::IDEA}()->first();
    event(new CommentPostedEvent($model));
    Idea::generateJson($idea);
    return $model;
  }

  public static function edit($input, $model)
  {
    $model->update($input);
    $idea = $model->{self::IDEA}()->first();
    event(new CommentPostedEvent($model));
    Idea::generateJson($idea);
    return $model;
  }

  public static function del($model)
  {
    $model->delete();
    return $model;
  }  
}
