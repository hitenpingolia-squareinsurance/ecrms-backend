<?php

namespace App\Http\Controllers\business_master;

use App\Http\Controllers\Controller;
use App\Models\business_master\Area;
use App\Models\business_master\City;
use App\Models\business_master\District;
use App\Models\business_master\Pincode;
use App\Models\business_master\State;
use Illuminate\Http\Request;
use App\Exports\BuninessDataExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class PinCodeController extends Controller
{
    public function index(Request $request)
    {
        $Page = $request->page;
        $size = $request->size;

        $PAGELIMIT = $size;
        $pageStart = ((int) $Page - 1) * $PAGELIMIT;

        $query = Pincode::with([
            'state' => function ($query) {
                $query->select('id', 'name');
            },
            'district' => function ($query) {
                $query->select('id', 'district_name');
            },
            'city' => function ($query) {
                $query->select('id', 'city_name');
            },
            'area' => function ($query) {
                $query->select('area_id', 'area_name');
            },
        ])
            ->select('id', 'pin_code', 'status', 'created_at', 'updated_at', 'state_id', 'district_id', 'city_id', 'area_id')
            ->where('status', '!=', 3)
            ->orderBy('id', 'desc');

        $query->when($request->search, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('pin_code', 'like', "%{$search}%")
                    ->orWhereHas('state', function ($query) use ($search) {
                        $query->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('district', function ($query) use ($search) {
                        $query->where('district_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('city', function ($query) use ($search) {
                        $query->where('city_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('area', function ($query) use ($search) {
                        $query->where('area_name', 'like', "%{$search}%");
                    });
            });
        });

        $query->when($request->state_id, function ($query, $state_id) {
            $query->where('state_id', $state_id);
        });

        $query->when($request->district_id, function ($query, $district_id) {
            $query->where('district_id', $district_id);
        });

        $query->when($request->city_id, function ($query, $city_id) {
            $query->where('city_id', $city_id);
        });

        $query->when($request->area_id, function ($query, $area_id) {
            $query->where('area_id', $area_id);
        });

        $total = $query->count();

        $results = $query->offset($pageStart)->limit($PAGELIMIT)->get();

        return response()->json([
            'status' => true,
            'data' => $results,
            'total' => $total,
        ], 200);
    }
    public function getStates()
    {
        $states = State::select('id', 'name')->get();

        return response()->json([
            'status' => true,
            'data' => $states,
        ], 200);
    }

    public function getDistricts($stateId)
    {
        $districts = District::select('id', 'district_name as name')->where('state_id', $stateId)->get();

        return response()->json([
            'status' => true,
            'data' => $districts,
        ], 200);
    }


    public function getCities($districtId)
    {
        $cities = City::select('id', 'city_name as name')->where('district_id', $districtId)->get();

        return response()->json([
            'status' => true,
            'data' => $cities,
        ], 200);
    }


    public function getAreas($cityId)
    {
        $areas = Area::select('area_id as id','area_name as name')->where('city_id', $cityId)->get();

        return response()->json([
            'status' => true,
            'data' => $areas,
        ], 200);
    }

    public function show($id)
    { 
        $data = [];

        $pincode = Pincode::select('state_id','district_id','city_id','area_Id as area_id','pin_code')->find($id);

        if (!$pincode) {
            return response()->json(['message' => 'Pincode not found'], 200);
        }

        $state_id = $pincode['state_id'];
        $district_id = $pincode['district_id']; 
        $city_id = $pincode['city_id'];
        $area_id = $pincode['area_id'];

        $data['row'] = $pincode;

        $data['states'] = State::select('id','name')->get();
        $data['districts'] = District::select('id','district_name as name')->where('state_id','=',$state_id)->get();
        $data['cities'] = City::select('id','city_name as name')->where('id','=',$city_id)->get();
        $data['areas'] = Area::select('area_id as id','area_name as name')->where('area_id','=',$area_id)->get();
 

        return response()->json(['data' => $data], 200);
    }

    public function save(Request $request)
    {
        $validatedData = $request->validate([
            'id' => 'nullable|integer|exists:geo_pincode,id',
            'state_id' => 'required|exists:states,id',
            'district_id' => 'required|exists:geo_districts,id',
            'city_id' => 'required|exists:geo_cities,id',
            'area_id' => 'required|exists:geo_areas,area_id',
            'pin_code' => 'required|string|max:10',
        ]);

        $pincode = Pincode::firstOrNew(['id' => $validatedData['id'] ?? null]);
        $pincode->state_id = $validatedData['state_id'];
        $pincode->district_id = $validatedData['district_id'];
        $pincode->city_id = $validatedData['city_id'];
        $pincode->area_id = $validatedData['area_id'];
        $pincode->pin_code = $validatedData['pin_code'];

        $pincode->save();

        return response()->json([
            'success' => true,
            'message' => $validatedData['id'] ? 'Pincode updated successfully' : 'Pincode added successfully'
        ], 200);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:geo_pincode,id',
            'status' => 'required|integer|in:1,2',
        ]);

        $pincode = Pincode::find($request->id);
        $pincode->status = $request->status;
        $pincode->save();

        return response()->json(['status' => true, 'message' => 'Status updated successfully']);
    }
    public function removePincode(Request $request)
    {
        $id = $request->query('id');

        $serviceLocation = Pincode::find($id);

        if ($serviceLocation) {
            $serviceLocation->status = 3;
            $serviceLocation->updated_at = now();
            $serviceLocation->save();

            $response = ['status' => true, 'message' => 'Pincode removed successfully'];
        } else {
            $response = ['status' => false, 'message' => 'Pincode not found'];
        }

        return response()->json($response);
    }
    public function export(Request $request)
    {
        $modelType = $request->input('modelType', 'pincode');

        return Excel::download(new BuninessDataExport($modelType), $modelType . '-' . Carbon::now()->format('dmy') . '.xlsx');
    }
}
