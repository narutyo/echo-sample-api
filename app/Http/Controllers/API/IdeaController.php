<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\ApiBaseController;
use App\Http\Requests\API\IdeaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Idea;

class IdeaController extends ApiBaseController
{
    public function store(IdeaRequest $request)
    {
      Log::info('Start create Idea');
      try {
        $idea = Idea::add($request->validated());
        Log::info('Create Idea success');
        return $this->success(
          'create_Idea',
          'Idea saved successfully',
          $this->apiSpecBaseUrl . '/create_Idea',
          $request,
          collect([$idea])->count(),
          $idea->toArray()
        );
      } catch (\Throwable $e) {
        return $this->sendError($e->getMessage(), 500);
      }
    }

    public function update(Idea $idea, IdeaRequest $request)
    {
      Log::info('Start update Idea');
      try {
        $idea = Idea::edit($request->validated(), $idea);
        Log::info('update Idea success');
        return $this->success(
          'update_Idea',
          'Idea updated successfully',
          $this->apiSpecBaseUrl . '/update_Idea',
          $request,
          collect([$idea])->count(),
          $idea->toArray()
        );
      } catch (\Throwable $e) {
        return $this->sendError($e->getMessage(), 500);
      }
    }

    public function delete(Idea $idea, Request $request)
    {
      Log::info('Start delete Idea', $request->all());
      try {
        $idea = Idea::del($idea);
        Log::info('delete Idea success');
        return $this->success(
          'delete_Idea',
          'Idea delete successfully',
          $this->apiSpecBaseUrl . '/delete_Idea',
          $request,
          collect([$idea])->count(),
          $idea->toArray()
        );
      } catch (\Throwable $e) {
        return $this->sendError($e->getMessage(), 500);
      }
    }

    //
    protected function model()
    {
      return Idea::class;
    }

    protected function getTypeIndex($request)
    {
      return $this->apiSpecBaseUrl . '/get_Ideas';
    }

    protected function getTypeShow()
    {
      return $this->apiSpecBaseUrl . '/get_Idea';
    }

    protected function getTypeDestroy()
    {
      return $this->apiSpecBaseUrl . '/delete_Idea';
    }
}
