<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function index(Request $request){

        $Page = $request->page;
		$size = $request->size;

        $PAGELIMIT = $size;
		$pageStart=((int)$Page-1)*$PAGELIMIT;

		$search = $request->search;

        if(!empty($search) && $search!='null'){
            $search = $request->search;
		}else{
            $search = '';
        }


        $type = '2';
              
        $query = User::select('id','name','username','email','mobile_no','status','created_at','updated_at');

        $query->when($type, function ($query, $type) {
            $query->where('type', '=', $type);
        });

        if (!empty($search)) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
            });
        }
         
        $total = $query->count();

        $results = $query->offset($pageStart)->limit($PAGELIMIT)->get();

        //dd($results);
		$response = array('status'=> true,'data'=>$results,'total' => $total);

		 return response()->json($response);

    }

    public function details(Request $request){

        $id = $request->id;


        $sql = "select 
					a.id,
					a.name,
                    a.username,
                    a.email,
                    a.mobile_no,
                    a.personal_email,
                    a.personal_mobile_no,
                    date_format(a.doj,'%d-%m-%Y') as doj,
                    date_format(a.dob,'%d-%m-%Y') as dob,
                    a.reporting_id,
                    a.vertical_id,
                    a.designation_id,
                    a.coreline_id,
                    a.zone_id,
                    a.profile_id,
                    a.regional_id,
                    a.branch_id,
                    a.service_location_id,

                    CASE
                        WHEN b.is_sales='1' THEN 'Sales'
                        ELSE 'Non Sales'
                    END as is_sales,
                    b.user_type, 

                    b.solicitor_type,
                    b.bqc_no,
                    b.saiba_id,
                    b.bqc_validity_from,
                    b.bqc_validity_to,
                    b.gender,
                    b.aadhar_no,
                    b.pancard_no,
                    b.address,
                    b.pincode,
                    b.blood_group,
                    b.emergency_contact_no,
                    b.father_name,
                    b.experience_status,
                    b.total_experience,
                    b.org_name,
                    b.last_org_contact_name,
                    b.last_org_start_date,
                    b.last_org_end_date,
                    b.qualification,
                    b.current_ctc 
				from 
				users as a
                inner join users_details as b on b.user_id=a.id
				where a.id=$id";  
		//echo $sql;  

        $row = DB::select($sql)[0];
         

        $reporting_id = $row->reporting_id;

        $zone_id = $row->zone_id;
        $vertical_id = $row->vertical_id;
        $designation_id = $row->designation_id; 
        $coreline_id = $row->coreline_id;
       

        $profile_id = $row->profile_id;
        $regional_id = $row->regional_id;
        $branch_id = $row->branch_id;
        $service_location_id = $row->service_location_id;

        if(!empty($reporting_id)){
            $reporting = DB::select("SELECT name FROM users WHERE id=$reporting_id");
            $row->rm_name = $reporting[0]->name;
       }

        if(!empty($zone_id)){
             $zone = DB::select("SELECT Group_concat(name) as name FROM zones WHERE status=1 AND id IN ($zone_id)");
             $row->zone_name = $zone[0]->name;
        }
        if(!empty($vertical_id)){
            $vertical = DB::select("SELECT Group_concat(name) as name FROM departments WHERE status=1 AND id IN ($vertical_id)");
            $row->vertical_name = $vertical[0]->name;
        }

        if(!empty($regional_id)){
            $regional = DB::select("SELECT name FROM regional_office WHERE id IN ($regional_id)");
            $row->regional_name = $regional[0]->name;
        }

        if(!empty($branch_id)){
            $branch = DB::select("SELECT Group_concat(name) as name FROM branches WHERE status=1 AND id IN ($branch_id)");
            $row->branch_name = $branch[0]->name;
        }

        if(!empty($service_location_id)){
            $service_location = DB::select("SELECT Group_concat(name) as name FROM sub_branches WHERE status=1 AND id IN ($service_location_id)");
            $row->service_location_name = $service_location[0]->name;
        }


        if(!empty($designation_id)){
            $designation = DB::select("SELECT Group_concat(name) as name FROM employee_master WHERE master_type='Designation' AND status=1 AND id IN ($designation_id)");
            $row->designation_name = $designation[0]->name;
        }

        if(!empty($coreline_id)){
            $coreline = DB::select("SELECT Group_concat(name) as name FROM employee_master WHERE master_type='Coreline' AND status=1 AND id IN ($coreline_id)");
            $row->coreline_name = $coreline[0]->name;
        }

        if(!empty($profile_id)){
            $profile = DB::select("SELECT Group_concat(name) as name FROM employee_master WHERE master_type='Profile' AND status=1 AND id IN ($profile_id)");
            $row->profile_name = $profile[0]->name;
        }


        

        
        $row->previous_id =$this->next_previous_id($id,'next');
        $row->next_id = $this->next_previous_id($id,'previous');


        //$paginates = User::where(array('type'=>'2','id'=>$id))->paginate(1);


        $response = array('status'=> false,'data'=>$row);

		return response()->json($response);



    }

    public function next_previous_id($id,$type){

        if($type == 'next'){
            $sql = "SELECT id FROM users WHERE type='2' and id = (select max(id) from users where type='2' and id < $id)";
        }else {
            $sql = "SELECT id FROM users WHERE type='2' and id = (select min(id) from users where type='2' and id > $id)";
            
        }

        $row = DB::select($sql);

        if(isset($row[0]->id) && !empty($row[0]->id)){
            return $row[0]->id;
        }else{
            return 0;
        }
        
    }
}
