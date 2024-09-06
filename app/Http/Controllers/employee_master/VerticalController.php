<?php

namespace App\Http\Controllers\employee_master;

use App\Http\Controllers\Controller;
use App\Models\Employee\Department;
use Illuminate\Http\Request;
use App\Exports\ExportData;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
class VerticalController extends Controller
{

    public function index(Request $request)
    {
        $Page = $request->page;
		$size = $request->size;

        $PAGELIMIT = $size;
		$pageStart=((int)$Page-1)*$PAGELIMIT;

        $search = $request->search ?? '';

        $query = Department::select('id', 'name', 'status', 'add_stamp', 'update_stamp')
            ->where('status', '!=', 3)
            ->orderBy('id', 'desc');

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

    public function saveVertical(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'nullable|integer|exists:departments,id',
                'name' => 'required|string|max:255',
            ]);

            if (isset($validatedData['id'])) {
                // Update Vertical
                $vertical = Department::find($validatedData['id']);
                if (!$vertical) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vertical not found.',
                    ], 404);
                }
                $vertical->update([
                    'name' => $validatedData['name'],
                    'update_stamp' => now(),
                ]);
                $message = 'Vertical updated successfully.';
            } else {
                // Create new Vertical
                $vertical = Department::create([
                    'name' => $validatedData['name'],
                    'status' => $request->input('status', 1),
                    'add_stamp' => now(),
                ]);
                $message = 'Vertical created successfully.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save Vertical',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function removeItem(Request $request)
    {
        $id = $request->query('id');

        $department = Department::find($id);

        if ($department) {
            $department->status = 3;
            $department->update_stamp = now();
            $department->save();

            $response = ['status' => true, 'message' => 'Vertical removed successfully'];
        } else {
            $response = ['status' => false, 'message' => 'Vertical not found'];
        }

        return response()->json($response);
    }
    public function updateStatus(Request $request)
    {

        $request->validate([
            'id' => 'required|integer|exists:departments,id',
            'status' => 'required|string|in:1,2',
        ]);

        $vertical = Department::find($request->id);
        if (!$vertical) {
            return response()->json(['status' => false,'message' => 'Vertical not found'], 404);
        }
        $vertical->status = $request->status;
        $vertical->save();
        return response()->json(['status' => true,'message' => 'Status updated successfully']);
        
    }
    public function export(Request $request)
    {
        $modelType = $request->input('modelType', 'department');
        
        return Excel::download(new ExportData($modelType), $modelType . '-' . Carbon::now()->format('Ymd') . '.xlsx');
       
    }
    

}



