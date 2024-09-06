<?php

namespace App\Http\Controllers\employee_master;

use App\Http\Controllers\Controller;
use App\Models\Employee\SabBranch;
use App\Models\Employee\Zone;
use App\Models\Employee\RegionalOffice;
use App\Models\Employee\Branch;
use Illuminate\Http\Request;
use App\Exports\ExportData;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ServiceLocationController extends Controller
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

        $query = SabBranch::select('id', 'name', 'status', 'zone_id', 'branch_id', 'ro_id', 'current_tier', 'add_stamp', 'update_stamp')
            ->where('status', '!=', 3)
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('add_stamp', 'desc')
            ->orderBy('name', 'asc')
            ->with(['zone:id,name', 'branch:id,name', 'regionalOffice:id,name']);

        $total = $query->count();

        $results = $query->offset($pageStart)->limit($PAGELIMIT)->get();

        return response()->json([
            'status' => true,
            'data' => $results,
            'total' => $total,

        ], 200);
    }

    public function getZones()
    {
        $zones = Zone::select('id', 'name')->get();
        return response()->json(['zones' => $zones], 200);
    }
    public function getRegionalOfficesByZone(Request $request)
    {
        $zoneId = $request->input('zone_id');
        $regionalOffices = RegionalOffice::where('zone_id', $zoneId)->select('id', 'name')->get();

        return response()->json(['regionalOffices' => $regionalOffices], 200);
    }

    public function getBranchesByRegionalOffice(Request $request)
    {
        $roId = $request->input('ro_id');
        $branches = Branch::where('ro_id', $roId)->select('id', 'name')->get();

        return response()->json(['branches' => $branches], 200);
    }
    public function getAllData()
    {
        $zones = Zone::select('id', 'name')->get();
        $regionalOffices = RegionalOffice::select('id', 'name')->get();
        $branches = Branch::select('id', 'name')->get();

        return response()->json([
            'zones' => $zones,
            'regionalOffices' => $regionalOffices,
            'branches' => $branches,
        ], 200);
    }

    public function saveServiceLocation(Request $request)
    {
        $validatedData = $request->validate([
            'service_location' => 'required|string|max:255',
            'zone' => 'required|integer',
            'regional_office' => 'required|integer',
            'branch' => 'required|integer',
            'tier' => 'required|string|in:Tier 1,Tier 2,Tier 3',
        ]);

        if ($request->has('id')) {
            // Update service location
            $serviceLocation = SabBranch::findOrFail($request->id);
            $serviceLocation->update([
                'name' => $validatedData['service_location'],
                'zone_id' => $validatedData['zone'],
                'ro_id' => $validatedData['regional_office'],
                'branch_id' => $validatedData['branch'],
                'current_tier' => $validatedData['tier'],
                'update_stamp' => now(),
            ]);
            $message = 'Service Location updated successfully';
            $statusCode = 200;
        } else {
            // Create new service location
            $serviceLocation = SabBranch::create([
                'name' => $validatedData['service_location'],
                'zone_id' => $validatedData['zone'],
                'ro_id' => $validatedData['regional_office'],
                'branch_id' => $validatedData['branch'],
                'current_tier' => $validatedData['tier'],
                'status' => $request->input('status', 1),
                'add_stamp' => now(),
            ]);
            $message = 'Service Location created successfully';
            $statusCode = 200;
        }

        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $serviceLocation
        ], $statusCode);
    }

    public function removeServiceLocation(Request $request)
    {
        $id = $request->query('id');

        $serviceLocation = SabBranch::find($id);

        if ($serviceLocation) {
            $serviceLocation->status = 3;
            $serviceLocation->update_stamp = now();
            $serviceLocation->save();

            $response = ['status' => true, 'message' => 'Service Location removed successfully'];
        } else {
            $response = ['status' => false, 'message' => 'Service Location not found'];
        }

        return response()->json($response);
    }
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'status' => 'required|string|in:1,2',
        ]);

        $serviceLocation = SabBranch::find($request->id);

        if (!$serviceLocation) {
            return response()->json(['message' => 'Service Location not found'], 404);
        }

        $serviceLocation->status = $request->status;
        $serviceLocation->save();

        return response()->json(['message' => 'Status updated successfully']);
    }

    public function export(Request $request)
    {
        $modelType = $request->input('modelType', 'sab_branch');

        return Excel::download(new ExportData($modelType), $modelType . '-' . Carbon::now()->format('Ymd') . '.xlsx');
    }
}

