<?php

namespace App\Http\Controllers\employee_master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee\EmployeeMaster;
use App\Exports\ExportData;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
class EmployeeMasterController extends Controller
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

        $type = $request->type;

        $query = EmployeeMaster::select('id', 'name', 'status', 'insert_date', 'update_stamp')
            ->where('status', '!=', 3);

        if (in_array($type, ['Coreline', 'Designation', 'Profile'])) {
            $query->where('master_type', '=', $type);
        }

        if (!empty($search)) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $query->orderBy('id', 'desc');

        $total = $query->count();

        $results = $query->offset($pageStart)->limit($PAGELIMIT)->get();

        $response = array('status' => true, 'data' => $results, 'total' => $total);

        return response()->json($response);
    }

    public function save(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'sometimes|integer|exists:employee_master,id',
                'name' => 'required|string|max:255',
                'master_type' => 'required|string|in:Coreline,Designation,Profile',
            ]);

            if (isset($validatedData['id'])) {
                // Update record
                $employeeMaster = EmployeeMaster::where('id', $validatedData['id'])
                    ->where('master_type', $validatedData['master_type'])
                    ->first();

                if (!$employeeMaster) {
                    return response()->json([
                        'status' => false,
                        'message' => ucfirst($validatedData['master_type']) . ' not found',
                    ], 404);
                }

                $employeeMaster->update([
                    'name' => $validatedData['name'],
                    'update_stamp' => now(),
                ]);

                $message = ucfirst($validatedData['master_type']) . ' created successfully';

            } else {
                // Create new record
                $employeeMaster = EmployeeMaster::create([
                    'name' => $validatedData['name'],
                    'master_type' => $validatedData['master_type'],
                    'status' => 1,
                    'insert_date' => now(),
                ]);

                $message = ucfirst($validatedData['master_type']) . ' created successfully';

            }


            return response()->json([
                'status' => true,
                'message' => $message,
            ], 200);



        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to save ' . $request->input('master_type'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function updateStatus(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|integer|exists:employee_master,id',
                'status' => 'required|integer|in:1,2',
                'master_type' => 'required|string|in:Coreline,Designation,Profile',
            ]);

            $employeeMaster = EmployeeMaster::where('id', $validatedData['id'])
                ->where('master_type', $validatedData['master_type'])
                ->first();

            if (!$employeeMaster) {
                return response()->json([
                    'status' => false,
                    'message' => ucfirst($validatedData['master_type']) . ' not found',
                ], 404);
            }

            $employeeMaster->update([
                'status' => $validatedData['status'],
                'update_stamp' => now(),
            ]);

            return response()->json([
                'status' => true,
                'message' => ucfirst($validatedData['master_type']) . ' status updated successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update status for ' . $request->query('master_type'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function remove(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'required|integer|exists:employee_master,id',
                'master_type' => 'required|string|in:Coreline,Designation,Profile',
            ]);

            $employeeMaster = EmployeeMaster::where('id', $validatedData['id'])
                ->where('master_type', $validatedData['master_type'])
                ->first();

            if (!$employeeMaster) {
                return response()->json([
                    'status' => false,
                    'message' => ucfirst($validatedData['master_type']) . ' not found',
                ], 404);
            }

            $employeeMaster->update([
                'status' => 3,
                'update_stamp' => now(),
            ]);

            return response()->json([
                'status' => true,
                'message' => ucfirst($validatedData['master_type']) . ' removed successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to remove ' . $request->input('master_type'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function export(Request $request)
    {
        $modelType = $request->input('modelType', 'employee_master');
        $masterType = $request->input('masterType', 'Coreline');

        $validMasterTypes = ['Coreline', 'Designation', 'Profile'];
        if ($modelType === 'employee_master') {
            return Excel::download(new ExportData($modelType, $masterType), $masterType . '-' . Carbon::now()->format('Ymd') . '.xlsx');
        }

    }

}
