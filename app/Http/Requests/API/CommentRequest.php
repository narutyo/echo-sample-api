<?php

namespace App\Http\Requests\API;

use App\Http\Requests\API\BaseRequest;
use App\Models\Comment;

class CommentRequest extends BaseRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    $rules = [
      Comment::BODY => 'required|string',
    ];
    if (!is_object($this->comment)) {
      $rules[Comment::IDEA_ID] = 'required|string';
    }
    return $rules;
  }

  public function messages()
  {
    return [
    ];
  }
}
