<?php

namespace App\Http\Controllers\business_master;

use App\Http\Controllers\Controller;
use App\Models\business_master\Cpa;
use App\Models\business_master\Insurer;
use Illuminate\Http\Request;
use App\Exports\BuninessDataExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
class CpaController extends Controller
{
    public function index(Request $request)
    {
        $Page = $request->page;
        $size = $request->size;
        $insurerId = $request->insurer_id ?? '';

        $PAGELIMIT = $size;
        $pageStart = ((int) $Page - 1) * $PAGELIMIT;

        $search = $request->search ?? '';

        $query = Cpa::with([
            'insurer' => function ($q) {
                $q->select('id', 'name');
            }
        ])
            ->select([
                'id',
                'insurer_id',
                'pvt',
                'tw',
                'gcv',
                'misd',
                'effective_date',
                'pcv',
                'status',
                'created_at',
                'updated_at',
            ])
            ->where('status', '!=', 3)
            ->orderBy('id', 'desc');

        if (!empty($search)) {
            $query->whereHas('insurer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if (!empty($insurerId)) {
            $query->where('insurer_id', $insurerId);
        }

        $total = $query->count();

        $results = $query->offset($pageStart)->limit($PAGELIMIT)->get();

        return response()->json([
            'status' => true,
            'data' => $results,
            'total' => $total,
        ], 200);
    }

    public function getCompany()
    {
        $company = Insurer::select('id', 'name')->get();
        return response()->json([
            'status' => true,
            'data' => $company,
        ], 200);
    }
    public function saveCpa(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'nullable|integer|exists:insurer_cpa,id',
            'insurer_id' => 'required|exists:insurers,id',
            'pvt' => 'required|string|max:255',
            'tw' => 'required|string|max:255',
            'gcv' => 'required|string|max:255',
            'misd' => 'required|string|max:255',
            'pcv' => 'required|string|max:255',
            'effective_date' => 'required|date|before_or_equal:today',
        ]);

        $userId = auth()->id();

        $validatedData = array_map(function ($value) {
            return $value === 'undefined' ? null : $value;
        }, $validatedData);

        if (isset($validatedData['id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Updates are not allowed. Please use a new record for different data.',
            ], 400);
        }

        $cpa = new Cpa();

        $cpa->insurer_id = $validatedData['insurer_id'];
        $cpa->pvt = $validatedData['pvt'];
        $cpa->tw = $validatedData['tw'];
        $cpa->gcv = $validatedData['gcv'];
        $cpa->misd = $validatedData['misd'];
        $cpa->pcv = $validatedData['pcv'];
        $cpa->effective_date = $validatedData['effective_date'];

        $cpa->user_id = $userId;
        $cpa->created_at = now();
        $cpa->updated_at = now();

        $cpa->save();

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'CPA added successfully'
        ], 200);
    }



    public function removeCpa(Request $request)
    {
        $id = $request->query('id');

        $Cpa = Cpa::find($id);

        if ($Cpa) {
            $Cpa->status = 3;
            $Cpa->save();

            $response = ['status' => true, 'message' => 'Cpa removed successfully'];
        } else {
            $response = ['status' => false, 'message' => 'Cpa not found'];
        }

        return response()->json($response);
    }
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:insurer_cpa,id',
            'status' => 'required|integer|in:1,2',
        ]);

        $Cpa = Cpa::find($request->id);
        if (!$Cpa) {
            return response()->json(['status' => false, 'message' => 'Cpa not found'], 404);
        }

        $Cpa->status = $request->status;
        $Cpa->save();

        return response()->json(['status' => true, 'message' => 'Status updated successfully']);
    }
    public function export(Request $request)
    {
        $modelType = $request->input('modelType', 'cpa');

        return Excel::download(new BuninessDataExport($modelType), $modelType . '-' . Carbon::now()->format('dmy') . '.xlsx');
    }

}
