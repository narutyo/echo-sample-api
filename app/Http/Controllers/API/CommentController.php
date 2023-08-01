<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\ApiBaseController;
use App\Http\Requests\API\CommentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Comment;

class CommentController extends ApiBaseController
{
    public function store(CommentRequest $request)
    {
      Log::info('Start create Comment');
      try {
        $comment = Comment::add($request->validated());
        Log::info('Create Comment success');
        return $this->success(
          'create_Comment',
          'Comment saved successfully',
          $this->apiSpecBaseUrl . '/create_Comment',
          $request,
          collect([$comment])->count(),
          $comment->toArray()
        );
      } catch (\Throwable $e) {
        return $this->sendError($e->getMessage(), 500);
      }
    }

    public function update(Comment $comment, CommentRequest $request)
    {
      Log::info('Start update Comment');
      try {
        $comment = Comment::edit($request->validated(), $comment);
        Log::info('update Comment success');
        return $this->success(
          'update_Comment',
          'Comment updated successfully',
          $this->apiSpecBaseUrl . '/update_Comment',
          $request,
          collect([$comment])->count(),
          $comment->toArray()
        );
      } catch (\Throwable $e) {
        return $this->sendError($e->getMessage(), 500);
      }
    }

    public function delete(Comment $comment, Request $request)
    {
      Log::info('Start delete Comment', $request->all());
      try {
        $comment = Comment::del($comment);
        Log::info('delete Comment success');
        return $this->success(
          'delete_Comment',
          'Comment delete successfully',
          $this->apiSpecBaseUrl . '/delete_Comment',
          $request,
          collect([$comment])->count(),
          $comment->toArray()
        );
      } catch (\Throwable $e) {
        return $this->sendError($e->getMessage(), 500);
      }
    }

    //
    protected function model()
    {
      return Comment::class;
    }

    protected function getTypeIndex($request)
    {
      return $this->apiSpecBaseUrl . '/get_Comments';
    }

    protected function getTypeShow()
    {
      return $this->apiSpecBaseUrl . '/get_Comment';
    }

    protected function getTypeDestroy()
    {
      return $this->apiSpecBaseUrl . '/delete_Comment';
    }
}
