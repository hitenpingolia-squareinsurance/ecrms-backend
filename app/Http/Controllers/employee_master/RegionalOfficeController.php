<?php

namespace App\Http\Controllers\employee_master;

use App\Http\Controllers\Controller;
use App\Models\Employee\RegionalOffice;
use Illuminate\Http\Request;
use App\Models\Employee\Zone;
use App\Exports\ExportData;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
class RegionalOfficeController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->page;
        $size = $request->size;

        $PAGELIMIT = $size;
        $pageStart = ((int) $page - 1) * $PAGELIMIT;

        $search = $request->search;

        if (empty($search) || $search === 'null') {
            $search = '';
        }
        $query = RegionalOffice::with('zone')
            ->select('id', 'name', 'status', 'add_stamp', 'update_stamp', 'zone_id')
            ->where('status', '!=', 3)
            ->orderBy('name', 'desc');

        if (!empty($search)) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%");
            });
        }

        $total = $query->count();

        $results = $query->offset($pageStart)->limit($PAGELIMIT)->get();

        $response = array('status' => true, 'data' => $results, 'total' => $total);

        return response()->json($response);
    }

    public function getZones()
    {
        $zones = Zone::select('id', 'name')->get();
        return response()->json(['zones' => $zones], 200);
    }

    public function saveRegionalOffice(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'nullable|integer|exists:regional_office,id',
                'name' => 'required|string|max:255',
                'zone' => 'required|exists:zones,id',
            ]);

            if (isset($validatedData['id'])) {
                // Update  Regional Office
                $regionalOffice = RegionalOffice::find($validatedData['id']);
                if (!$regionalOffice) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Regional Office not found.',
                    ], 404);
                }
                $regionalOffice->update([
                    'name' => $validatedData['name'],
                    'zone_id' => $validatedData['zone'],
                    'update_stamp' => now(),
                ]);
                $message = 'Regional Office updated successfully.';
            } else {
                // Create new Regional Office
                $regionalOffice = RegionalOffice::create([
                    'name' => $validatedData['name'],
                    'zone_id' => $validatedData['zone'],
                    'status' => $request->input('status', 1),
                    'add_stamp' => now(),
                ]);
                $message = 'Regional Office created successfully.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save Regional Office',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function removeRegionalOffice(Request $request)
    {
        $id = $request->query('id');

        $RegionalOffice = RegionalOffice::find($id);

        if ($RegionalOffice) {
            $RegionalOffice->status = 3;
            $RegionalOffice->update_stamp = now();
            $RegionalOffice->save();

            $response = ['status' => true, 'message' => 'Regional Office removed successfully'];
        } else {
            $response = ['status' => false, 'message' => 'Regional Office not found'];
        }

        return response()->json($response);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:regional_office,id',
            'status' => 'required|string|in:1,2',
        ]);

        $regionalOffice = RegionalOffice::find($request->id);

        if (!$regionalOffice) {
            return response()->json(['message' => 'Regional Office not found'], 404);
        }

        $regionalOffice->status = $request->status;
        $regionalOffice->save();

        return response()->json(['message' => 'Status updated successfully'], 200);
    }

    public function export(Request $request)
    {
        $modelType = $request->input('modelType', 'regional_office');

        return Excel::download(new ExportData($modelType), $modelType . '-' . Carbon::now()->format('Ymd') . '.xlsx');
    }

}
