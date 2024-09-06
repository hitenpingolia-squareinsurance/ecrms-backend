<?php

namespace App\Http\Controllers\business_master;
use App\Http\Controllers\Controller;
use App\Models\business_master\Rto;
use App\Models\business_master\State;
use Illuminate\Http\Request;
use App\Exports\BuninessDataExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
class RtoController extends Controller
{
    public function index(Request $request)
    {
        $Page = $request->page;
        $size = $request->size;
        $PAGELIMIT = $size;
        $pageStart = ((int) $Page - 1) * $PAGELIMIT;

        $search = $request->search ?? '';
        $stateId = $request->state_id;

        $query = Rto::select('id', 'name', 'code', 'state_id', 'status', 'created_at', 'updated_at')
            ->with([
                'state' => function ($q) {
                    $q->select('id', 'name');
                }
            ])
            ->where('status', '!=', 3)
            ->orderBy('id', 'desc');

        if (!empty($search) && $search !== 'null') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (!empty($stateId) && $stateId !== 'null') {
            $query->where('state_id', $stateId);
        }

        $total = $query->count();

        $results = $query->offset($pageStart)->limit($PAGELIMIT)->get();

        return response()->json([
            'status' => true,
            'data' => $results,
            'total' => $total,
        ], 200);
    }

    public function getStates()
    {
        $states = State::select('id', 'name')->get();

        return response()->json([
            'status' => true,
            'data' => $states,
        ], 200);
    }

    public function saveRto(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'nullable|integer|exists:rto,id',
            'name' => 'required|string|max:255',
            'code' => 'required',
            'state_id' => 'required|exists:states,id',
        ]);

        $rto = Rto::firstOrNew(['id' => $validatedData['id'] ?? null]);
        $rto->state_id = $validatedData['state_id'];
        $rto->name = $validatedData['name'];
        $rto->code = $validatedData['code'];

        if (!$rto->exists) {
            $rto->add_stamp = now();
        }
        $rto->update_stamp = now();

        if ($rto->save()) {
            return response()->json([
                'success' => true,
                'message' => $validatedData['id'] ? 'Rto updated successfully' : 'Rto added successfully'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save Rto'
            ], 422);
        }
    }
    public function removeRto(Request $request)
    {
        $id = $request->query('id');

        $Rto = Rto::find($id);

        if ($Rto) {
            $Rto->status = 3;
            $Rto->save();

            $response = ['status' => true, 'message' => 'Rto removed successfully'];
        } else {
            $response = ['status' => false, 'message' => 'Rto not found'];
        }

        return response()->json($response);
    }
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:rto,id',
            'status' => 'required|integer|in:1,2',
        ]);

        $Rto = Rto::find($request->id);
        if (!$Rto) {
            return response()->json(['status' => false, 'message' => 'Rto not found'], 404);
        }

        $Rto->status = $request->status;
        $Rto->save();

        return response()->json(['status' => true, 'message' => 'Status updated successfully']);
    }
    public function export(Request $request)
    {
        $modelType = $request->input('modelType', 'rto');

        return Excel::download(new BuninessDataExport($modelType), $modelType . '-' . Carbon::now()->format('dmy') . '.xlsx');
    }

}
