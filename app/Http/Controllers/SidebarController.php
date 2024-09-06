<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
 

class SidebarController extends Controller
{

 

    public function index(){

        $response = array();
        $menus = array();

        $results = DB::table('sidebar_menu')
            ->select('id','name','route','icon')
            ->where('parent_id',0)
            ->where('status',1)
			->orderBy('sequence','asc')
            ->get();

        if(!empty($results)){

            foreach($results as $row){

                if(!empty($row->route)){
                    if($row->name == 'Dashboard'){

                        $menus[] = [
                            'title' => $row->name,
                            'icon' => $row->icon,
                            'link' => $row->route,
                            'home' => true,
                        ];

                        $menus[] = [
                            'title' => 'FEATURES',
                            'group' => true,
                        ];

                    }else{
                        $menus[] = [
                            'title' => $row->name,
                            'icon' => $row->icon,
                            'link' => $row->route,
                        ];
                    }
                    

                }else{
                    $sub_menu = $this->sub_menu($row->id);
                
                    $menus[] = [
                        'title' => $row->name,
                        'icon' => $row->icon,
                        'children' => $sub_menu,
        
                    ];
                }
                

            } 
            $response = array('status'=> true,'data'=>$menus);
        }else{
            $response = array('status'=> false,'data'=>$menus);
        }

  
        return response()->json($response);

    }

    public function sub_menu($parent_id)
	{
		

        $results = DB::table('sidebar_menu')
                ->select('name','route')
                ->where('parent_id',$parent_id)
                ->where('status',1)
                ->orderBy('sequence','asc')
                ->get();

        // $sql = "select name,route from sidebar_menu where parent_id=$parent_id and status=1 order by sequence asc";
        // $results = $this->db->query($sql)->result();
        
        $menus = array();
        if(!empty($results)){
            foreach($results as $row){
                $menus[] = [
                    'title' => $row->name,
                    'link' => $row->route,
                ];
            }
        }
        return $menus;

	}


    public function getparentMenus(Request $request)
	{
		$id = $request->id;

		$data = [];
		
        $sql = "select id,name from sidebar_menu where parent_id=0 and status=1 order by name asc";
        $results = DB::select($sql);

		$data['parentMenus'] = $results;
		$data['rowMenu'] = [];

		if(!empty($id)){

			$sql = "select type,parent_id,name,icon,route,is_view,is_export,is_action,action_label from sidebar_menu where id=$id limit 1";
        	$rowMenu = DB::select($sql)[0];

			$data['rowMenu'] = array(
				'type' => $rowMenu->type,
				'menu_id' => ($rowMenu->type==2)?$rowMenu->parent_id:0,
				'title' => $rowMenu->name,
				'route' => $rowMenu->route,
				'icon' => ($rowMenu->type==1)?$rowMenu->icon:'',
				'is_view' => ($rowMenu->is_view==1)?true:false,
				'is_export' => ($rowMenu->is_export==1)?true:false,
				'is_action' => ($rowMenu->is_action==1)?true:false,
				'action_label' => ($rowMenu->is_action==1)?$rowMenu->action_label:'',
			);
		}
		

		$response = array('status'=> true,'data'=>$data);

		return response()->json($response);

	}

	

	public function add_item(Request $request)
	{
         

        $id = $request->id;

		$type = $request->type;

		$save = array();

		
		$save['type'] = $type;
		$save['parent_id'] = ($type==2)?$request->menu_id:0;

		$save['name'] = $request->title;
		$save['route'] = (isset($request->route) && !empty($request->route))?$request->route:'';  
		$save['icon'] = (isset($request->icon) && !empty($request->icon))?$request->icon:'';
		
		$save['is_view'] = (isset($request->is_view) && $request->is_view == 'true')?1:0;
		$save['is_export'] = (isset($request->is_export) && $request->is_export == 'true')?1:0;
		$save['is_action'] = (isset($request->is_action) && $request->is_action == 'true')?1:0;
		$save['action_label'] = (isset($request->is_action) && $request->is_action == 'true')?$request->action_label:'';

		$save['status'] = 1;
 
        //dd($save);  

		if(empty($id)){

			$save['add_stamp'] = DateTime();
			$save['update_stamp'] = DateTime();

            DB::table('sidebar_menu')->insert($save);

			$response = array('status'=> true,'message'=> 'Menu Item added successfully' );

            return response()->json($response);

		}else{
			$save['update_stamp'] = DateTime();
 
			DB::table('sidebar_menu')->where('id',$id)->update($save);

			$response = array('status'=> true,'message'=> 'Menu Item update successfully' );

            return response()->json($response);

		}

		

		

	}

	public function removeItem(Request $request)
	{
		 
		$id = $request->id;
         
		$save =[];

		$save['status'] = 0;
		$save['update_stamp'] = DateTime();

        DB::table('sidebar_menu')
                ->where('id',$id)
                ->update($save); 

		$response = array('status'=> true,'message'=> 'Menu Item removed successfully' );

		return response()->json($response);

	}


    public function records(Request $request)
	{

        
        

		$Page = $request->page;
		$size = $request->size;

		$search = $request->search;

		$whereAr = [];
		$whereAr[] = "status=1";

		if(!empty($search) && $search!='null'){
			$whereAr[] = "(name like '%$search%' OR route like '%$search%')";
		}

		$where = implode(' and ',$whereAr);

		$PAGELIMIT = $size;
		$pageStart=((int)$Page-1)*$PAGELIMIT;

		$sql = "select 
					id,
					name as title,route,
					date_format(add_stamp,'%d-%m-%Y %h:%i:%s %p') as add_stamp,
					date_format(update_stamp,'%d-%m-%Y %h:%i:%s %p') as update_stamp 
				from 
				sidebar_menu 
				where $where 
				order by id desc 
				limit $pageStart,$PAGELIMIT"; //
		//echo $sql; die;

        $results = DB::select($sql);


		$sql1 = "select COUNT(id) as total_count from sidebar_menu where $where";
        $total_res = DB::select($sql1);
        $total = $total_res[0]->total_count;

		$response = array('status'=> false,'data'=>$results,'total' => $total);

		 return response()->json($response);

	}

}
