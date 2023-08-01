<?php

namespace App\Http\Controllers;

class AppBaseController extends Controller
{
    public function sendSuccess($message)
    {
        return response()->json([
            'success' => true,
            'message' => $message
        ], 200);
    }

    public function sendError($error, $code = 404)
    {
        return response()->json(static::makeError($error), $code);
    }

    protected static function makeError($message, array $data = [])
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

    protected function success($title, $detail, $type, $request, $count, $results)
    {
        $data = [
            'metadata' => [
                'title' => $title,
                'detail' => $detail,
                'type' => $type,
                'parameter' => $request->except([
                    'offset',
                    'limit',
                ]),
                'resultset' => [
                    'count' => $count,
                    'offset' => $request->has('offset') ? (int)$request->get('offset') : null,
                    'limit' => $request->has('limit') ? (int)$request->get('limit') : null
                ],
            ],
            'results' => $results
        ];
        return response()->json($data, 200);
    }
}
