<?php

namespace App\Http\Controllers\business_master;

use App\Http\Controllers\Controller;
use App\Models\business_master\Broker;
use Illuminate\Http\Request;
use App\Exports\BuninessDataExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
class BrokerController extends Controller
{
    public function index(Request $request)
    {
        $Page = $request->page;
        $size = $request->size;

        $PAGELIMIT = $size;
        $pageStart = ((int) $Page - 1) * $PAGELIMIT;

        $search = $request->search ?? '';

        $query = Broker::select('id', 'name', 'status', 'created_at', 'updated_at')->where('status', '!=', 3)
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
    public function saveBroker(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'id' => 'nullable|integer|exists:broker,id',
                'name' => 'required|string|max:255',
            ]);

            if (isset($validatedData['id'])) {
                // Update Broker
                $Broker = Broker::find($validatedData['id']);
                if (!$Broker) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Broker not found.',
                    ], 404);
                }
                $Broker->update([
                    'name' => $validatedData['name'],
                    'updated_at' => now(),
                ]);
                $message = 'Broker updated successfully.';
            } else {
                // Create new Broker
                $Broker = Broker::create([
                    'name' => $validatedData['name'],
                    'status' => $request->input('status', 1),
                    'created_at' => now(),
                ]);
                $message = 'Broker created successfully.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save Broker',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function removeBroker(Request $request)
    {
        $id = $request->query('id');

        $Broker = Broker::find($id);

        if ($Broker) {
            $Broker->status = 3;
            $Broker->save();

            $response = ['status' => true, 'message' => 'Broker removed successfully'];
        } else {
            $response = ['status' => false, 'message' => 'Broker not found'];
        }

        return response()->json($response);
    }
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:Broker,id',
            'status' => 'required|integer|in:1,2',
        ]);

        $Broker = Broker::find($request->id);
        if (!$Broker) {
            return response()->json(['status' => false, 'message' => 'Broker not found'], 404);
        }

        $Broker->status = $request->status;
        $Broker->save();

        return response()->json(['status' => true, 'message' => 'Status updated successfully']);
    }
    public function export(Request $request)
    {
        $modelType = $request->input('modelType', 'broker');

        return Excel::download(new BuninessDataExport($modelType), $modelType . '-' . Carbon::now()->format('dmy') . '.xlsx');
    }
}
