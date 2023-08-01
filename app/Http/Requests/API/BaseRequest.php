<?php

namespace App\Http\Requests\API;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Response;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BaseRequest extends FormRequest
{
  public static function makeResponse($message, $data)
  {
    return [
      'success' => true,
      'data'    => $data,
      'message' => $message,
    ];
  }

  public static function makeError($message, array $data = [])
  {
    $res = [
      'success' => false,
      'message' => $message,
    ];

    if (!empty($data)) {
      $res['data'] = $data;
    }

    return $res;
  }

  public function response(array $errors)
  {
    $messages = implode(' ', Arr::flatten($errors));

    return Response::json(static::makeError($messages), 400);
  }

  public function validator($factory)
  {
    return $factory->make(
      $this->sanitizeInput(),
      $this->container->call([$this, 'rules']),
      $this->messages()
    );
  }

  protected function sanitizeInput()
  {
    if (method_exists($this, 'sanitize')) {
        $this->container->call([$this, 'sanitize']);
    }
    return $this->all();
  }

  protected function clearControlChars($mixed)
  {
    if (is_array($mixed)) {
      return array_map(function ($item) {
        return $this->clearControlChars($item);
      }, $mixed);
    }

    return preg_replace('/[\x00-\x09\x0b\x0c\x0e-\x1f\x7f]/', '', $mixed);
  }

  protected function convertKana($content)
  {
    return trim(preg_replace('/\S\r\n+/u', ' ', mb_convert_kana($content, 'asKV')));
  }
}
