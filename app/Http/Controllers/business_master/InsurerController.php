<?php

namespace App\Http\Controllers\business_master;

use App\Http\Controllers\Controller;
use App\Models\business_master\Insurer;
use Illuminate\Http\Request;
use App\Exports\BuninessDataExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
class InsurerController extends Controller
{
    public function index(Request $request)
    {
        $Page = $request->page;
        $size = $request->size;

        $PAGELIMIT = $size;
        $pageStart = ((int) $Page - 1) * $PAGELIMIT;

        $search = $request->search ?? '';

        $query = Insurer::select('id', 'name', 'motor', 'health', 'non_motor', 'life', 'travel', 'pa', 'status', 'created_at', 'updated_at')->where('status', '!=', 3)
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

    public function saveInsurer(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'id' => 'nullable|integer|exists:insurers,id',
            'name' => 'required|string',
            'motor' => 'nullable|boolean',
            'health' => 'nullable|boolean',
            'non_motor' => 'nullable|boolean',
            'life' => 'nullable|boolean',
            'travel' => 'nullable|boolean',
            'pa' => 'nullable|boolean',
        ]);

        $fields = [
            'name' => $validatedData['name'],
            'motor' => $validatedData['motor'] ?? 0,
            'health' => $validatedData['health'] ?? 0,
            'non_motor' => $validatedData['non_motor'] ?? 0,
            'life' => $validatedData['life'] ?? 0,
            'travel' => $validatedData['travel'] ?? 0,
            'pa' => $validatedData['pa'] ?? 0,
        ];

        if (isset($validatedData['id'])) {
            $insurer = Insurer::find($validatedData['id']);
            if (!$insurer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insurer not found'
                ], 404);
            }
            $insurer->update(array_merge($fields, [
                'updated_at' => now(),
            ]));
            $message = 'Insurer updated successfully';
        } else {
            $insurer = Insurer::create(array_merge($fields, [
                'created_at' => now(),
            ]));
            $message = 'Insurer added successfully';
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ], 200);
    }

    public function show($id)
    {
        $insurer = Insurer::select('name', 'motor', 'health', 'non_motor', 'life', 'travel', 'pa')
            ->find($id);

        if (!$insurer) {
            return response()->json(['message' => 'Insurer not found'], 404);
        }

        return response()->json(['data' => ['row' => $insurer]], 200);
    }

    public function removeInInsurer(Request $request)
    {
        $id = $request->query('id');

        $InInsurer = Insurer::find($id);

        if ($InInsurer) {
            $InInsurer->status = 3;
            $InInsurer->save();

            $response = ['status' => true, 'message' => 'InInsurer removed successfully'];
        } else {
            $response = ['status' => false, 'message' => 'InInsurer not found'];
        }

        return response()->json($response);
    }
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:insurers,id',
            'status' => 'required|integer|in:1,2',
        ]);

        $InInsurer = Insurer::find($request->id);
        if (!$InInsurer) {
            return response()->json(['status' => false, 'message' => 'InInsurer not found'], 404);
        }

        $InInsurer->status = $request->status;
        $InInsurer->save();

        return response()->json(['status' => true, 'message' => 'Status updated successfully']);
    }
    public function export(Request $request)
    {
        $modelType = $request->input('modelType', 'insurer');

        return Excel::download(new BuninessDataExport($modelType), $modelType . '-' . Carbon::now()->format('dmy') . '.xlsx');
    }
}
