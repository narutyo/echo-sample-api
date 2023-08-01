<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

abstract class ApiBaseController extends AppBaseController
{
    public const DEFAULT_OFFSET = 0;
    public const DEFAULT_LIMIT = 20;
    public const OFFSET = 'offset';
    public const LIMIT = 'limit';

    protected $model;
    protected $apiSpecBaseUrl;
    protected $type;

    public function __construct()
    {
        $this->model = app()->make($this->model());
        $this->apiSpecBaseUrl = config('consts.api_spec_url');
    }

    public function index(Request $request)
    {
        Log::info('Start get list ' . class_basename($this->model));
        $offset = $request->has('offset') ? $request->get('offset') : self::DEFAULT_OFFSET;
        $limit = $request->has('limit') ? $request->get('limit') : self::DEFAULT_LIMIT;
        $fields = $request->has('fields') ? $request->get('fields') : [];
        if (!$request->has('offset') || !$request->has('limit')) {
            if ($request->has('page') && is_numeric($request->get('page'))) {
                $offset = ($request->get('page') - 1) * $limit;
            }
        }
        $criteria = $this->where($request);
        $query = $this->search($criteria, $fields);
        $count = $query->count();
        if (!is_null($offset)) {
            $query->offset($offset);
        }
        if (!is_null($limit)) {
            $query->limit($limit);
        }
        $data = $query->get();

        $data->each(function ($value, $key) use ($fields) {
            $this->setVisibleFields($value, $fields);
        });

        Log::info('Get list ' . class_basename($this->model) . ' success');
        return $this->success(
            'index',
            'Retrieved successfully',
            $this->getTypeIndex($request),
            $request,
            $count,
            $data->toArray(),
        );
    }

    public function show($id, Request $request)
    {
        Log::info('Start get detail ' . class_basename($this->model));
        $fields = $request->has('fields') ? $request->get('fields') : [];
        $data = method_exists($this->model, 'findByKey')
            ? $this->model->findByKey($id, $request)
            : $data = $this->model->find($id);
        if (empty($data)) {
            return $this->sendError('Data not found');
        }

        $this->setVisibleFields($data, $fields);
        Log::info('Get detail ' . class_basename($this->model) . ' success');
        return $this->success(
            'Detail',
            'Data retrieved successfully',
            $this->getTypeShow(),
            $request,
            collect([$data])->count(),
            $data->toArray()
        );
    }
    
    public function destroy($id, Request $request)
    {
        Log::info('Start delete ' . class_basename($this->model));
        $data = $this->find($id);
        if (empty($data)) {
            return $this->sendError('Data not found');
        }
        $data->delete();
        event(new Notification($this->channel()));
        Log::info('Delete ' . class_basename($this->model) . ' success');
        return $this->success(
            'Delete',
            'Data deleted successfully',
            $this->getTypeDestroy(),
            $request,
            0,
            null
        );
    }

    abstract protected function model();

    protected function find($id)
    {
        $query = $this->model->newQuery();
        $data = $query->find($id);
        return $data;
    }

    protected function where($request)
    {
        return $request->all();
    }

    protected function search($criteria, $fields)
    {
        return $this->model->search($criteria);
    }

    protected function mapSearchableFieldsToRelation($with, $fields)
    {
        if (!empty($fields)) {
            $with = array_filter($with, function ($val, $key) use ($fields) {
                return in_array($key, $fields) || in_array($val, $fields);
            }, ARRAY_FILTER_USE_BOTH);
        }
        return $with;
    }

    protected function getTypeIndex($request)
    {
        return $this->apiSpecBaseUrl;
    }

    protected function getVisibleFields()
    {
        return [$this->model::ID];
    }

    protected function setVisibleFields($data, $fields)
    {
      $data->setVisible($this->getVisibleFields());
      $fillable = $data->getFillable();
      if (!empty($fields) && (array_intersect($fillable, $fields) || !empty($data->getRelations()))) {
        $relations = array_keys($data->getRelations());
        $fields = array_merge($fields, $relations);
        $data->setVisible($fields);
      }
    }

}
