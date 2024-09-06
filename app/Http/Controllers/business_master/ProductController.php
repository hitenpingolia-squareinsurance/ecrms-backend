<?php

namespace App\Http\Controllers\business_master;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    
    public function index(){

    }

    public function filters(){

        $data = [];

        $data['lob'] = Product::select('lob as name')->groupBy('lob')->get();

        $response = array('status'=> true,'data'=>$data);

		return response()->json($response);

    }

    public function getProducts(Request $request){

        $lob = (!empty($request->lob) && $request->lob!='null')?$request->lob:'';
        $insurer_id = (!empty($request->insurer_id) && $request->insurer_id!='null')?$request->insurer_id:'0';

        $data = [];

        $data['insurers'] = [];
        
        $query = Product::select('product as short_name','product as name');

        $query->when($lob, function ($query, $lob) {
            $query->where('lob', '=', $lob);
        });

 
        $query->when($insurer_id, function ($query, $insurer_id) {
            $query->where('insurer_id', '=', $insurer_id);
        });

        $data['products'] =   $query->groupBy('product')->get();

        if($lob == 'Health'){
            $data['insurers'] = DB::select("SELECT id,name FROM `insurers` where health=1");
        }
        
        $response = array('status'=> true,'data'=>$data);

		return response()->json($response);

    }

    public function getPolicyType(Request $request){

        $lob = (!empty($request->lob) && $request->lob!='null')?$request->lob:'';
        $product = (!empty($request->product) && $request->product!='null')?$request->product:'';
        $insurer_id = (!empty($request->product) && $request->insurer_id!='null')?$request->insurer_id:'';

        $data = [];
        if($lob == 'Health'){
            $data['policy_types'] = Product::select('policy_type','policy_type as short_name')
                ->where('lob', '=', $lob)
                ->where('insurer_id', '=', $insurer_id)
                ->where('product', '=', $product)
                ->groupBy('policy_type')
                ->get();
        }else{
            $data['policy_types'] = Product::select('policy_type','policy_type as short_name')
            ->where('lob', '=', $lob)
            ->where('product', '=', $product)
            ->groupBy('policy_type')
            ->get();
        }

        $response = array('status'=> true,'data'=>$data);

		return response()->json($response);

    }


    
    public function getPlanType(Request $request){

        $lob = (!empty($request->lob) && $request->lob!='null')?$request->lob:'';
        $product = (!empty($request->product) && $request->product!='null')?$request->product:'';
        $policy_type = (!empty($request->policy_type) && $request->policy_type!='null')?$request->policy_type:'';
        $insurer_id = (!empty($request->product) && $request->insurer_id!='null')?$request->insurer_id:'';

        $data = [];
        
        if($lob == 'Health'){
            $data['plan_types'] = Product::select('plan_type as name')
                ->where('lob', '=', $lob)
                ->where('insurer_id', '=', $insurer_id)
                ->where('product', '=', $product)
                ->where('policy_type', '=', $policy_type)
                ->where('plan_type', '!=','')
                ->groupBy('plan_type')
                ->get();
        }else{
            $data['plan_types'] = Product::select('plan_type as name')
                ->where('lob', '=', $lob)
                ->where('product', '=', $product)
                ->where('policy_type', '=', $policy_type)
                ->where('plan_type', '!=','')
                ->groupBy('plan_type')
                ->get();
        }

        $response = array('status'=> true,'data'=>$data);

		return response()->json($response);

    }

    

    public function list(Request $request){

        $Page = $request->page;
		$size = $request->size;

        $PAGELIMIT = $size;
		$pageStart=((int)$Page-1)*$PAGELIMIT;

	 
        $search = (!empty($request->search) && $request->search!='null')?$request->search:'';
        $lob = (!empty($request->lob) && $request->lob!='null')?$request->lob:'';
        $product = (!empty($request->product) && $request->product!='null')?$request->product:'';
        $policy_type = (!empty($request->policy_type) && $request->policy_type!='null')?$request->policy_type:'';
        $plan_type = (!empty($request->plan_type) && $request->plan_type!='null')?$request->plan_type:'';
        $insurer_id = (!empty($request->insurer) && $request->insurer!='null')?$request->insurer:'';
              
        $query = Product::select('id','lob','product','policy_type','plan_type','sub_product','status','created_at','updated_at');

        $query->whereIn('status',[1,2]);

        $query->when($lob, function ($query, $lob) {
            $query->where('lob', '=', $lob);
        });
        $query->when($product, function ($query, $product) {
            $query->where('product', '=', $product);
        });
        $query->when($policy_type, function ($query, $policy_type) {
            $query->where('policy_type', '=', $policy_type);
        });
        $query->when($plan_type, function ($query, $plan_type) {
            $query->where('plan_type', '=', $plan_type);
        });

        $query->when($insurer_id, function ($query, $insurer_id) {
            $query->where('insurer_id', '=', $insurer_id);
        });

        if (!empty($search)) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('product', 'like', "%{$search}%")
                         ->orWhere('policy_type', 'like', "%{$search}%")
                         ->orWhere('plan_type', 'like', "%{$search}%")
                         ->orWhere('sub_product', 'like', "%{$search}%") 
                         ->orWhere('class_name', 'like', "%{$search}%")
                         ->orWhere('sub_class', 'like', "%{$search}%"); 
            });
        }
         
        $total = $query->count();

        $results = $query->offset($pageStart)->limit($PAGELIMIT)->get();

        //dd($results);
		$response = array('status'=> true,'data'=>$results,'total' => $total);

		return response()->json($response);

    }


    public function removeItem(Request $request)
	{
		 
		$id = $request->id;
         
		$save =[];

		$save['status'] = 3;
		$save['updated_at'] = DateTime();

        DB::table('product_master')
                ->where('id',$id)
                ->update($save); 

		$response = array('status'=> true,'message'=> 'Product removed successfully' );

		return response()->json($response);

	}

}
