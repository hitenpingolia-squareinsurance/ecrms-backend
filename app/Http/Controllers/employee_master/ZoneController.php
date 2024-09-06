<?php

namespace App\Http\Controllers\employee_master;

use App\Http\Controllers\Controller;
use App\Models\Employee\Zone;
use Illuminate\Http\Request;


class ZoneController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $size = $request->input('size', 10);
        $search = $request->input('search', '');

        $pageLimit = $size;
        $pageStart = ($page - 1) * $pageLimit;

        $query = Zone::select('id', 'name', 'status', 'add_stamp', 'update_stamp', )->whereNot('status', 3);

        if (!empty($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        $total = $query->count();
        $results = $query->offset($pageStart)->limit($pageLimit)->get();

        return response()->json([
            'status' => true,
            'data' => $results,
            'total' => $total,

        ], 200);
    }
}
