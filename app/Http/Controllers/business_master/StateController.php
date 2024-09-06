<?php

namespace App\Http\Controllers\business_master;

use App\Http\Controllers\Controller;
use App\Models\business_master\State;
use App\Models\Employee\Zone;
use Illuminate\Http\Request;
use App\Exports\BuninessDataExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
class StateController extends Controller
{
    public function index(Request $request)
    {
        $Page = $request->page;
        $size = $request->size;

        $PAGELIMIT = $size;
        $pageStart = ((int) $Page - 1) * $PAGELIMIT;

        $search = $request->search ?? '';

        $query = State::with('zone')->select('id', 'zone_id', 'name', 'created_at', 'updated_at');


        if (!empty($search) && $search !== 'null') {
            $query->where('name', 'like', "%{$search}%");
        }

        $total = $query->count();

        $results = $query->offset($pageStart)->limit($PAGELIMIT)->get();

        return response()->json([
            'status' => true,
            'data' => $results,
            'total' => $total,
        ], 200);
    }
    public function saveState(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'nullable|integer|exists:states,id',
                'name' => 'required|string|max:255',
                'zone_id' => 'required|integer|exists:zones,id',
                'status' => 'nullable|integer',
            ]);

            if ($request->has('id')) {
                $state = State::find($request->input('id'));

                $state->update([
                    'name' => $validatedData['name'],
                    'zone_id' => $validatedData['zone_id'],
                    'updated_at' => now(),
                ]);
                $message = 'State updated successfully.';
            }
            return response()->json([
                'success' => true,
                'message' => $message,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save State',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
    public function getZones()
    {
        $zones = Zone::select('id', 'name')->get();
        return response()->json(['zones' => $zones], 200);
    }
    public function removeState(Request $request)
    {
        $id = $request->query('id');

        $State = State::find($id);

        if ($State) {
            $State->status = 3;
            $State->save();

            $response = ['status' => true, 'message' => 'State removed successfully'];
        } else {
            $response = ['status' => false, 'message' => 'State not found'];
        }

        return response()->json($response);
    }
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:States,id',
            'status' => 'required|integer|in:1,2',
        ]);

        $State = State::find($request->id);
        if (!$State) {
            return response()->json(['status' => false, 'message' => 'State not found'], 404);
        }

        $State->status = $request->status;
        $State->save();

        return response()->json(['status' => true, 'message' => 'Status updated successfully']);
    }
    public function export(Request $request)
    {
        $modelType = $request->input('modelType', 'state');

        return Excel::download(new BuninessDataExport($modelType), $modelType . '-' . Carbon::now()->format('dmy') . '.xlsx');
    }

}
