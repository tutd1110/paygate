<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SMSTemplate\SMSTemplateRequest;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageTemplateController extends Controller
{
    function __construct(MessageTemplate $messageTemplate)
    {
        $this->messageTemplate = $messageTemplate;
    }

    public function index(Request $request)
    {
        $filter = $request->all();
        $query = $this->messageTemplate::query()->with('landingPage');
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
        if (isset($filter['event'])) {
            if (is_array($filter['event'])) {
                $query = $query->whereIn('event', $filter['event']);
            } else {
                $query = $query->where('event', $filter['event']);
            }
        }
        if (isset($filter['status'])) {
            if (is_array($filter['status'])) {
                $query = $query->whereIn('status', $filter['status']);
            } else {
                $query = $query->where('status', $filter['status']);
            }
        }
        if (isset($filter['landing_page_id'])) {
            if (is_array($filter['landing_page_id'])) {
                $query = $query->whereIn('landing_page_id', $filter['landing_page_id']);
            } else {
                $query = $query->where('landing_page_id', $filter['landing_page_id']);
            }
        }
        $messageTemplate = $query
            ->paginate($request->get('limit', config('cms.limit')));
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => [
                'messageTemplate' => $messageTemplate,
            ]
        ]);
    }

    public function store(SMSTemplateRequest $request)
    {
        $filter = $request->validated();
        $filter['parent_id'] = 0;
        DB::beginTransaction();
        try {
            if ($filter) {
                $messageTemplate = $this->messageTemplate->create($filter);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => [
                        'messageTemplate' => $messageTemplate,
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

    public function update(Request $request, $id)
    {
        $data = $request->all();
//        $data = $request->validated();
        DB::beginTransaction();
        try {
            $messageTemplate = $this->messageTemplate::find($id);
            $messageTemplate->fill($data);
            $messageTemplate->save();
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'update success',
                'data' => [
                    'messageTemplate' => $messageTemplate
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

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $messageTemplate = $this->messageTemplate::find($id);
            $messageTemplate->delete();
            DB::commit();
            return response()->json([
                'message' => 'delete success',
                'data' => [
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
}
