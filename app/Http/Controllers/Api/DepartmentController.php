<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\DepartmentFilterRequest;
use App\Http\Requests\Department\DepartmentRequest;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /***
     * @var Department
     */
    public $_mainModel;

    public function __construct(Department $department)
    {
        $this->_mainModel = $department;
    }

    /***
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(DepartmentFilterRequest $request)
    {
        $filter = $request->all();
        $query = $this->_mainModel;

        if (isset($filter['id'])) {
            if (is_array($filter['id'])) {
                $query = $query->whereIn('id', $filter['id']);
            } else {
                $query = $query->where('id', $filter['id']);
            }
        }

        if (isset($filter['code'])) {
            if (is_array($filter['code'])) {
                $query = $query->whereIn('code', $filter['code']);
            } else {
                $query = $query->where('code', $filter['code']);
            }
        }

        if (isset($filter['name'])) {
            if (is_array($filter['name'])) {
                $query = $query->whereIn('name', 'like', $filter['name'].'%');
            } else {
                $query = $query->where('name', 'like', $filter['name'].'%');
            }
        }

        if (isset($filter['brand_id'])) {
            if (is_array($filter['brand_id'])) {
                $query = $query->whereIn('brand_id', $filter['brand_id']);
            } else {
                $query = $query->where('brand_id', $filter['brand_id']);
            }
        }

        if (isset($filter['is_active'])) {
            $query = $query->where('is_active', $filter['is_active']);
        }

        if (isset($filter['order'])) {
            if (is_array($filter['order'])) {
                foreach ($filter['order'] as $key => $value) {
                    $query = $query->orderBy($value, $filter['direction'][$key] ?? 'asc');
                }
            } else {
                $query = $query->orderBy($filter['order'], $filter['direction'] ?? 'asc');
            }
        }
        // convert id to key
        if (isset($filter['get_all'])) {
            $query = $query->get();
            $departments = [];
            foreach ($query as $key => $value){
                $departments[$value['id']] = $value;
            }
        }else {
            $departments = $query->paginate($request->get('limit', 20));
        }

        return response()->json([
            'message' => 'get success',
            'data' => [
                'departments' => $departments
            ]
        ]);

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(DepartmentRequest $request)
    {
        $department = $this->_mainModel->create($request->all());

        return response()->json([
            'message' => 'save success',
            'data' => [
                'deparment' => $department
            ]
        ]);

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $department = $this->_mainModel->find($id);

        return response()->json([
            'message' => 'get success',
            'data' => [
                'deparment' => $department
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(DepartmentRequest $request, $id)
    {
        $department = $this->_mainModel->find($id);

        $department->fill($request->validated());
        $save = $department->save();

        return response()->json([
            'message' => 'update success',
            'data' => [
                'deparment' => $department
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $department = $this->_mainModel->find($id);

        $department->delete();

        return response()->json([
            'message' => 'delete success',
            'data' => [
            ]
        ]);
    }
}
