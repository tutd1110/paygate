<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\EmailTemplate\EmailTemplateRequest;


class EmailTemplateController extends Controller
{
    function __construct(EmailTemplates $emailTemplates)
    {
        $this->emailTemplates = $emailTemplates;
    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->emailTemplates::query();
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
            $query = $query->where('name', 'like', '%' . $filter['name'] . '%');
        }
        if (isset($filter['status'])) {
            if (is_array($filter['status'])) {
                $query = $query->whereIn('status', $filter['status']);
            } else {
                $query = $query->where('status', $filter['status']);
            }
        }
        $emailTemplate = $query
            ->paginate($request->get('limit', config('cms.limit')));
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'emailTemplate' => $emailTemplate,
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EmailTemplateRequest $request)
    {
        $filter = $request->validated();
        DB::beginTransaction();
        try {
            if ($filter) {
                $emailTemplates = $this->emailTemplates->create($filter);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'emailTemplates' => $emailTemplates,
                    ]
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'message' => $e->getMessage(),
                    'type' => 'error',
                ]
            ]);
        }
    }

    public function update(EmailTemplateRequest $request, $id)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            $emailTemplates = $this->emailTemplates::find($id);
            $emailTemplates->fill($data);
            $emailTemplates->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'emailTemplates' => $emailTemplates
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'message' => $e->getMessage(),
                    'type' => 'error',
                ]
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $emailTemplates = $this->emailTemplates::find($id);
            $emailTemplates->delete();
            DB::commit();
            return response()->json([
                'message' => 'delete success',
                'data' => []
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'data' => [
                    'message' => $e->getMessage(),
                    'type' => 'error',
                ]
            ]);
        }
    }
}
