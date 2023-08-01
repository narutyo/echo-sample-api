<?php

namespace App\Http\Requests\API;

use App\Http\Requests\API\BaseRequest;
use App\Models\Idea;

class IdeaRequest extends BaseRequest
{
  public function authorize()
  {
    return true;
  }

  public function rules()
  {
    $rules = [
      Idea::TITLE => 'required|string',
      Idea::BODY => 'required|string',
    ];
    return $rules;
  }

  public function messages()
  {
    return [
    ];
  }
}
