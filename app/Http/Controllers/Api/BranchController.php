<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchRequest;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{


    /***
     * @var Branch
     */
    public $_mainModel;

    public function __construct(Branch $branch)
    {
        $this->_mainModel = $branch;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $this->_mainModel;

        $filter = $request->all();
        if (isset($filter['order'])) {
            if (is_array($filter['order'])) {
                foreach ($filter['order'] as $key => $value) {
                    $query = $query->orderBy($value, $filter['direction'][$key] ?? 'asc');
                }
            } else {
                $query = $query->orderBy($filter['order'], $filter['direction'] ?? 'asc');
            }
        }

        if ($request->get('count')) {
            $branchs = $query->paginate($request->get('limit', 20));
        } else {
            $branchs = $query->simplePaginate($request->get('limit', 20));
        }

        return response()->json([
            'message' => 'get success',
            'data' => [
                'branchs' => $branchs
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
    public function store(BranchRequest $request)
    {
        $branch = $this->_mainModel->create($request->all());

        return response()->json([
            'message' => 'save success',
            'data' => [
                'branch' => $branch
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
        $branch = $this->_mainModel->find($id);

        return response()->json([
            'message' => 'get success',
            'data' => [
                'branch' => $branch
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
    public function update(BranchRequest $request, $id)
    {
        $branch = $this->_mainModel->find($id);

        $branch->fill($request->validated());
        $save = $branch->save();

        return response()->json([
            'message' => 'update success',
            'data' => [
                'branch' => $branch
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
        $branch = $this->_mainModel->find($id);

        $branch->delete();

        return response()->json([
            'message' => 'delete success',
            'data' => [
            ]
        ]);
    }
}
