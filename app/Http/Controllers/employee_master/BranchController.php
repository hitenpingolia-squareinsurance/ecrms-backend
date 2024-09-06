<?php

namespace App\Http\Controllers\employee_master;

use App\Http\Controllers\Controller;
use App\Models\Employee\Branch;
use App\Models\Employee\Zone;
use App\Models\Employee\RegionalOffice;
use Illuminate\Http\Request;
use App\Exports\ExportData;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;


class BranchController extends Controller
{
    public function index(Request $request)
    {

        $page = $request->page;
        $size = $request->size;

        $PAGELIMIT = $size;
        $pageStart = ((int) $page - 1) * $PAGELIMIT;

        $search = $request->search;

        if (!empty($search) && $search !== 'null') {
            $search = $request->search;
        } else {
            $search = '';
        }


        $query = Branch::with(['zone', 'regionalOffice'])
            ->select('id', 'name', 'zone_id', 'ro_id', 'status', 'address', 'address_link', 'add_stamp', 'update_stamp')
            ->where('status', '!=', 3)
            ->orderBy("id", "desc");

        if (!empty($search)) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $results = $query->offset($pageStart)->limit($PAGELIMIT)->get();

        return response()->json([
            'status' => true,
            'data' => $results,
            'total' => $total,

        ], 200);

    }


    public function getAllData()
    {
        try {
            $zones = Zone::select('id', 'name')->get();
            $regionalOffices = RegionalOffice::select('id', 'name')->get();

            return response()->json([
                'zones' => $zones,
                'regionalOffices' => $regionalOffices,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch all data',
                'error' => $th->getMessage(),
            ], 500);
        }
    }


    public function getZones()
    {
        $zones = Zone::select('id', 'name')->get();
        return response()->json(['zones' => $zones], 200);
    }

    public function getRegionalOffices(Request $request)
    {
        $regionalOffices = RegionalOffice::select('id', 'name')->get();
        return response()->json(['regionalOffices' => $regionalOffices], 200);

    }

    public function saveBranch(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required',
                'zone_id' => 'required|integer|exists:zones,id',
                'ro_id' => 'required|integer|exists:regional_office,id',
                'address_link' => 'nullable|string|max:255',
            ]);

            if ($request->has('id')) {
                // Update branch
                $branch = Branch::findOrFail($request->input('id'));
                $branch->update([
                    'name' => $validatedData['name'],
                    'zone_id' => $validatedData['zone_id'],
                    'ro_id' => $validatedData['ro_id'],
                    'address' => $validatedData['address'],
                    'address_link' => $validatedData['address_link'],
                    'update_stamp' => now(),
                ]);

                $message = 'Branch updated successfully.';
                $status = 200;
            } else {
                // Create new branch
                $branch = Branch::create([
                    'name' => $validatedData['name'],
                    'zone_id' => $validatedData['zone_id'],
                    'ro_id' => $validatedData['ro_id'],
                    'status' => $request->input('status', 1),
                    'address' => $validatedData['address'],
                    'address_link' => $validatedData['address_link'],
                    'add_stamp' => now(),
                ]);

                $message = 'Branch created successfully.';
                $status = 201;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $branch,
            ], $status);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save Branch',
                'error' => $th->getMessage(),
            ], 500);
        }
    }


    public function removeBranch(Request $request)
    {
        $id = $request->query('id');

        $branch = Branch::find($id);

        if ($branch) {
            $branch->status = 3;
            $branch->update_stamp = now();
            $branch->save();

            $response = ['status' => true, 'message' => 'Branch removed successfully'];
        } else {
            $response = ['status' => false, 'message' => 'Branch not found'];
        }

        return response()->json($response);
    }
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:branches,id',
            'status' => 'required|string|in:1,2',
        ]);
        $branch = Branch::find($request->id);

        if (!$branch) {
            return response()->json(['message' => 'Branch not found'], 404);
        }

        $branch->status = $request->status;
        $branch->save();

        return response()->json(['message' => 'Status updated successfully'], 200);
    }
    public function export(Request $request)
    {
        $modelType = $request->input('modelType', 'branch');
        return Excel::download(new ExportData($modelType), $modelType . '-' . Carbon::now()->format('Ymd') . '.xlsx');
    }

}