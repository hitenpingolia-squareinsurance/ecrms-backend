<?php

namespace App\Http\Controllers;

use App\Models\Rights;
use App\Models\Sidebar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RightsController extends Controller
{

    public function index(Request $request){

        $user_id = $request->id;

        $response = array();
        $menus = array();

        $results = Sidebar::select('id','name')
            ->where('parent_id',0)
            ->where('status',1)
            ->orderBy('sequence','asc')
            ->get();

        if(!empty($results)){

            foreach($results as $row){


                //$sql1 = "select menu_type from users_rights where menu_type=1 and user_id=$user_id and menu_id=".$row->id;

                $isCheck = Rights::select('menu_type')->where(['menu_type'=>'1','user_id' => $user_id,'menu_id' => $row->id])->get(); 
             
                $sub_menu = $this->sub_menu($row->id,$user_id);
                $menus[] = [
                    'id' => $row->id,
                    'isChecked' => (isset($isCheck[0]) && !empty($isCheck[0]))?true:false,
                    'name' => $row->name,
                    'sub_menu' => $sub_menu
                ];

            } 
            $response = array('status'=> true,'data'=>$menus);
        }else{
            $response = array('status'=> false,'data'=>$menus);
        }

  
        return response()->json($response);

    }

    public function sub_menu($parent_id,$user_id)
	{
		

        $results = Sidebar::select('id','name','is_view','is_export','is_action','action_label')
                ->where('parent_id',$parent_id)
                ->where('status',1)
                ->orderBy('sequence','asc')
                ->get();


           

        $menus = array();
        if(!empty($results)){
            foreach($results as $row){


                $sql1 = "select menu_type,menu_is_view,menu_is_export,is_action_manager,excel_columns from users_rights where user_id=$user_id and menu_id=".$row->id;
                $is_Check = DB::select($sql1);

                $excel_columns = '';
                $excel_columns = (isset($is_Check[0]->excel_columns) && !empty($is_Check[0]->excel_columns))?$is_Check[0]->excel_columns:'';

                if($row->name == 'Employee'){  
                    $excel_columns = $this->columns('Employee',$excel_columns);  
                }
                if($row->name == 'Certified POSP'){  
                    $excel_columns = $this->columns('posp',$excel_columns);  
                }

                $is_view_type = 'Hierarchy';

                $menu_is_view = (isset($is_Check[0]) && !empty($is_Check[0]->menu_is_view) && $is_Check[0]->menu_is_view)?$is_Check[0]->menu_is_view:'';


                if($menu_is_view=='1'){
                    $is_view_type = 'All';
                }else if($menu_is_view=='2'){
                    $is_view_type = 'Hierarchy';
                }else if($menu_is_view=='3'){
                    $is_view_type = 'Self';
                }

                $menus[] = [
                    'id' => $row->id,
                    'isChecked' => (isset($is_Check[0]) && !empty($is_Check[0]))?true:false,
                    'name' => $row->name,
                    'is_view' => $row->is_view,
                    'is_view_type' => $is_view_type,//All,Hierarchy,Self

                    'isChecked_Export' => (isset($is_Check[0]) && !empty($is_Check[0]->menu_is_export) && $is_Check[0]->menu_is_export==1)?true:false,
                    'is_export' => $row->is_export,
                    'excel_columns' => $excel_columns,
                    
                    'isChecked_Action' => (isset($is_Check[0]) && !empty($is_Check[0]->is_action_manager) && $is_Check[0]->is_action_manager==1)?true:false,
                    'is_action' => $row->is_action,
                    'action_label' => $row->action_label,

                ];
            }
        }
        return $menus;

	}


    public function columns($type,$selectedColumns){

        $excel_columns=[];


        switch ($type) {
            case 'Employee':
              
                $excel_columns[] = ["is_checked" => true,"col" => "a.name",             "label" => "Name"];
                $excel_columns[] = ["is_checked" => true,"col" => "a.username",             "label" => "Employee_Code"];
                $excel_columns[] = ["is_checked" => true,"col" => "b.user_type",             "label" => "Employee_Type"];

                $excel_columns[] = ["is_checked" => true,"col" => "a.name as name",             "label" => "Email"];
                $excel_columns[] = ["is_checked" => true,"col" => "b.gender",             "label" => "Gender"];
                $excel_columns[] = ["is_checked" => true,"col" => "a.mobile_no",   "label" => "Mobile_No"];
                $excel_columns[] = ["is_checked" => false,"col" => "a.personal_email", "label" => "Personal_Email"];
                $excel_columns[] = ["is_checked" => false,"col" => "a.personal_mobile_no",   "label" => "Personal_Mobile_No"];
            
                $excel_columns[] = ["is_checked" => false,"col" => "a.doj as doj",     "label" => "Date_Of_Joining"];
                $excel_columns[] = ["is_checked" => false,"col" => "a.dob as dob",     "label" => "Date_Of_Brith"];
                $excel_columns[] = ["is_checked" => false,"col" => "b.blood_group",             "label" => "Blood_Group"];

                $excel_columns[] = ["is_checked" => true,"col" => "b.aadhar_no",             "label" => "Aadhar_No"];
                $excel_columns[] = ["is_checked" => true,"col" => "b.pancard_no",             "label" => "Pancard_No"];
                $excel_columns[] = ["is_checked" => false,"col" => "b.address",             "label" => "Address"];

                $excel_columns[] = ["is_checked" => true,"col" => "a.reporting_id as rm_name",       "label" => "RM_Name"];
                

                $excel_columns[] = ["is_checked" => false,"col" => "b.solicitor_type",             "label" => "Solicitor"];
                $excel_columns[] = ["is_checked" => false,"col" => "b.bqc_no",             "label" => "BQC_No"];
                $excel_columns[] = ["is_checked" => false,"col" => "b.saiba_id",             "label" => "Saiba_Id"];
                $excel_columns[] = ["is_checked" => false,"col" => "b.bqc_validity_from",             "label" => "BQC_Validity_From"];
                $excel_columns[] = ["is_checked" => false,"col" => "b.bqc_validity_to",             "label" => "BQC_Validity_To"];


                $excel_columns[] = ["is_checked" => false,"col" => "b.emergency_contact_no",             "label" => "Emergency_Contact_No"];
                $excel_columns[] = ["is_checked" => false,"col" => "b.father_name",             "label" => "Father_Name"];
                $excel_columns[] = ["is_checked" => false,"col" => "b.experience_status",             "label" => "Experience_Type"];
                $excel_columns[] = ["is_checked" => false,"col" => "b.total_experience",             "label" => "Total_Experience"];

                $excel_columns[] = ["is_checked" => false,"col" => "b.org_name",             "label" => "Org_Name"];
                $excel_columns[] = ["is_checked" => false,"col" => "b.last_org_contact_name",             "label" => "Last_Org_Contact_Name"];
                $excel_columns[] = ["is_checked" => false,"col" => "b.last_org_start_date",             "label" => "Last_Org_Start_Date"];
                $excel_columns[] = ["is_checked" => false,"col" => "b.last_org_end_date",             "label" => "Last_Org_End_Date"];

                $excel_columns[] = ["is_checked" => true,"col" => "a.created_at as dob",     "label" => "Create_Date"];

              break;
            case 'posp':
              //code block;

                $excel_columns[] = ["is_checked" => true,"col" => "a.name",             "label" => "Partner_Name"];
                $excel_columns[] = ["is_checked" => true,"col" => "a.username",             "label" => "Partner_Code"];
                $excel_columns[] = ["is_checked" => true,"col" => "b.user_type",             "label" => "Partner_Type"];

                $excel_columns[] = ["is_checked" => true,"col" => "a.name as name",             "label" => "Email"];
                $excel_columns[] = ["is_checked" => true,"col" => "b.gender",             "label" => "Gender"];
                $excel_columns[] = ["is_checked" => true,"col" => "a.mobile_no",   "label" => "Mobile_No"];
   
                $excel_columns[] = ["is_checked" => false,"col" => "a.dob as dob",     "label" => "Date_Of_Brith"];
                 
                $excel_columns[] = ["is_checked" => true,"col" => "b.aadhar_no",             "label" => "Aadhar_No"];
                $excel_columns[] = ["is_checked" => true,"col" => "b.pancard_no",             "label" => "Pancard_No"];
                $excel_columns[] = ["is_checked" => false,"col" => "b.address",             "label" => "Address"];

                $excel_columns[] = ["is_checked" => true,"col" => "a.reporting_id as rm_name",       "label" => "RM_Name"];
                

                $excel_columns[] = ["is_checked" => true,"col" => "a.created_at as dob",     "label" => "Create_Date"];


              break;
            case 'sr':
              //code block
              break;
            default:
              //code block
          }

        
        
        
        $revise_colums=[];
        $selectedColumnsAr = explode(',',$selectedColumns);

        if(!empty($selectedColumnsAr)){
            foreach($excel_columns as $col){
                if (in_array($col['label'], $selectedColumnsAr)){
                    $revise_colums[]=['is_checked'=> true,'label'=> $col['label'] ];
                }else{
                    $revise_colums[]=['is_checked'=> false,'label'=> $col['label'] ];
                }
                
            }
        }else{
            foreach($excel_columns as $col){
                $revise_colums[]=['is_checked'=> $col['is_checked'],'label'=> $col['label'] ]; 
            }
        }
        

        return $revise_colums;

    }

    public function assign(Request $request){

        $id = $request->input('id');
        $user_id = Encryptor('decrypt',$request->input('user_id'));
   
        $ar = json_decode($request->input('data'),true);
        
        $dateTime = DateTime();


        if(isset($ar) && !empty($ar)){
            $menus = [];
            foreach($ar as $row){
                if($row['isChecked'] == true){
                    $menus[] = [
                        'user_id' => $id,
                        'menu_id' => $row['id'],
                        'menu_type' => 1,
                        'menu_parent_id' => 0,
                        'menu_is_view' => 0,
                        'menu_is_export' => 0,
                        'excel_columns' => '',
                        'is_action_manager' => 0,
                        'add_stamp' => $dateTime,
                    ];

                    foreach($row['sub_menu'] as $submenu){
                        if($submenu['isChecked'] == true){

                            $is_view = '3';
                            if($submenu['is_view_type']=='All'){
                                 $is_view = '1';
                            }else if($submenu['is_view_type']=='Hierarchy'){
                                $is_view = '2';
                            }else if($submenu['is_view_type']=='Self'){
                                $is_view = '3';
                            }

                            $menus[] = [
                                'user_id' => $id,
                                'menu_id' => $submenu['id'],
                                'menu_parent_id' => $row['id'],
                                'menu_type' => 2,

                                'menu_is_view' => (!empty($submenu['is_view']) && $submenu['is_view']==1)?$is_view:0,

                                'menu_is_export' => (!empty($submenu['isChecked_Export']) && $submenu['isChecked_Export']==true)?1:0,
                                'excel_columns' => (!empty($submenu['isChecked_Export']) && $submenu['isChecked_Export']==true)?getExcelColumnCommaSeprated($submenu['excel_columns']):'',
                                
                                'is_action_manager' => (!empty($submenu['isChecked_Action']) && $submenu['isChecked_Action']==true)?1:0,

                                'add_stamp' => $dateTime,
                            ];
                        }//end if
                    }//end foreach


                }//end if
                
            }//end foreach

            if(!empty($menus)){
                DB::table('users_rights')->where('user_id', $id)->delete();
                DB::table('users_rights')->insert($menus);
            }
        }//end if
       

        return response()->json(['status' => true,'message' => 'Rights has been assinged successfully.','data' => [$menus,$ar]]);
        

    }

}
