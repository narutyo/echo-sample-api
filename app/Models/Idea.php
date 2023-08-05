<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;
use App\Events\IdeaPostedEvent;
use App\Events\IdeaDeletedEvent;

class Idea extends BaseModel
{
  public const TABLE_NAME = 'ideas';

  // リレーション
  public const COMMENTS = 'comments';

  // フィールド
  public const TITLE = 'title';
  public const BODY = 'body';

  public $fillable = [
    self::TITLE,
    self::BODY,
  ];

  protected $casts = [
    self::TITLE  => 'string',
    self::BODY  => 'string',
    self::CREATED_AT => 'datetime:' . self::DATE_FORMAT,
    self::UPDATED_AT => 'datetime:' . self::DATE_FORMAT,
  ];

  protected $hidden = [
    self::UPDATED_BY,
    self::CREATED_BY,
    self::DELETED_AT,
  ];

  public function comments()
  {
    return $this->hasMany('App\Models\Comment', Comment::IDEA_ID, self::ID);
  }
  
  public static function add($input)
  {
    $model = self::create($input)->fresh();
    event(new IdeaPostedEvent($model));
    self::generateIdeaJson();
    self::generateCommentJson($model);
    return $model;
  }

  public static function edit($input, $model)
  {
    $model->update($input);
    event(new IdeaPostedEvent($model));
    self::generateIdeaJson();
    self::generateCommentJson($model);
    return $model;
  }

  public static function del($model)
  {
    Storage::disk('public')->delete('comment_'.$model->id.'.json');
    $model->delete();
    event(new IdeaDeletedEvent($model));
    self::generateIdeaJson();
    return $model;
  }

  public static function generateIdeaJson()
  {
    $json = array();
    $json['ideas'] = self::orderBy(self::UPDATED_AT, 'DESC')->get()->toArray();

    Storage::disk('public')->put('ideas.json', json_encode($json));
  }

  public static function generateCommentJson($model)
  {
    $json = array();
    $json['idea'] = array(
      'id'  => $model->id,
      'title' => $model->title,
      'body'  => $model->body,
      'updated_at'  => $model->updated_at,
    );
    $json['comments'] = $model->{self::COMMENTS}()->get()->toArray();

    Storage::disk('public')->put('comment_'.$model->id.'.json', json_encode($json));
  }
}
