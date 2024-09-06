<?php

namespace App\Http\Controllers\business_master;

use App\Http\Controllers\Controller;
use App\Models\business_master\Bank;
use Illuminate\Http\Request;
use App\Exports\BuninessDataExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
class BankController extends Controller
{
    public function index(Request $request)
    {
        $Page = $request->page;
        $size = $request->size;

        $PAGELIMIT = $size;
        $pageStart = ((int) $Page - 1) * $PAGELIMIT;

        $search = $request->search ?? '';

        $query = Bank::select('id', 'name', 'status', 'created_at', 'updated_at')->where('status', '!=', 3)
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
    public function savebank(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'nullable|integer|exists:banks,id',
                'name' => 'required|string|max:255',
            ]);

            if (isset($validatedData['id'])) {
                // Update Bank
                $bank = Bank::find($validatedData['id']);
                if (!$bank) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bank not found.',
                    ], 404);
                }
                $bank->update([
                    'name' => $validatedData['name'],
                    'updated_at' => now(),
                ]);
                $message = 'Bank updated successfully.';
            } else {
                // Create new Bank
                $bank = Bank::create([
                    'name' => $validatedData['name'],
                    'status' => $request->input('status', 1),
                    'created_at' => now(),
                ]);
                $message = 'Bank created successfully.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save bank',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function removeBank(Request $request)
    {
        $id = $request->query('id');

        $bank = Bank::find($id);

        if ($bank) {
            $bank->status = 3;
            $bank->save();

            $response = ['status' => true, 'message' => 'Bank removed successfully'];
        } else {
            $response = ['status' => false, 'message' => 'Bank not found'];
        }

        return response()->json($response);
    }
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:banks,id',
            'status' => 'required|integer|in:1,2',
        ]);

        $bank = Bank::find($request->id);
        if (!$bank) {
            return response()->json(['status' => false, 'message' => 'Bank not found'], 404);
        }

        $bank->status = $request->status;
        $bank->save();

        return response()->json(['status' => true, 'message' => 'Status updated successfully']);
    }
    public function export(Request $request)
    {
        $modelType = $request->input('modelType', 'bank');

        return Excel::download(new BuninessDataExport($modelType), $modelType . '-' . Carbon::now()->format('dmy') . '.xlsx');
    }

}
