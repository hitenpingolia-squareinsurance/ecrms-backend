<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class AuthController extends Controller
{
    
    public function register(Request $request){

        // $validator = Validator::make($request->all(), [
        //     'name'      => 'required|string|max:255',
        //     'email'     => 'required|string|max:255|unique:users',
        //     'password'  => 'required|string'
        //   ]);

        // if ($validator->fails()) {
        //     return response()->json($validator->errors());
        // }

        // $user = User::create([
        //     'name'      => $request->name,
        //     'email'     => $request->email,
        //     'password'  => Hash::make($request->password)
        // ]);

        // $token = $user->createToken('auth_token')->plainTextToken;
        // return response()->json([
        //     'data'          => $user,
        //     'access_token'  => $token,
        //     'token_type'    => 'Bearer'
        // ]);

    }


    public function login(Request $request)
    {
        // Validate the request data
       
        $validator = Validator::make($request->all(), [
            'username'  => 'required|string'
          ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $username = $request->username;
        $password = $request->password;
        $type = $request->type; //type 1=otp login, 2= password-login
        $otp = $request->otp;


 
        $user = User::select('id','name','username','email','password','mobile_no','otp')
                ->where(function($query) use ($username){
                    $query->where('mobile_no', '=', $username)
                        ->orWhere('username', '=', $username);
                    })   
                ->where('status', '1')   
                ->first();

        //print_r($user); dd;


        if (!$user) {
            return response()->json(['status' => false,'message' => 'Incorrect Username/Mobile no.']);
        }

        if(empty($otp) && $type==1){
            //send otp

            $mobile_no = $user['mobile_no'];

            $otp = generateOTP();

            $sendOTP = sendOTP($otp,$mobile_no);

            if($sendOTP){

                $data = [];
                 
                // Set the expiration time in minutes
                $token_expire_at = Carbon::now()->addMinutes(10); // 120 minutes = 2 hours

                $token  = $user->createToken($user->name,[],$token_expire_at)->plainTextToken;

                // Return the token
                $data['id'] = Encryptor('encrypt',$user['id']);
                $data['token'] = $token;


                $update = array();
                $update['otp'] = $otp;

                User::where('id',$user['id'])->update($update); 

                return response()->json(['status' => true,'message' => 'OTP Sent Successfully.','data' => $data]);

            }else{
                return response()->json(['status' => false,'message' => 'OTP Sent Failed.','data' => []]);
            }
           
        }else if(empty(!$otp) && $type==1){
            //verify otp

            $db_otp = $user['otp'];

            if($otp == $db_otp){

                return response()->json(['status' => true,'message' => 'OTP Verify Successfully.','data' => $this->sessionData($user)]);

            }else{
                return response()->json(['status' => false,'message' => 'Please enter vaild OTP !']);
            }


        }else{
            //password login

            $hashed = $user['password'];

            if(VerifyPassword($password,$hashed) == 1){
  

                return response()->json(['status' => true,'message' => 'Login Successfully.','data' => $this->sessionData($user)]);
                

            }else{
                return response()->json(['status' => false,'message' => 'Incorrect Password !']);
            } 

        }

       
        
    }


    public function generate_qr(){
        // Generate a unique token for the user
        $token = Str::random(32);

        $qrCode = QrCode::size(200)->color(75, 0, 130)->generate($token);
        echo $qrCode;

        // $qrCode = QrCode::size(200)
        //             ->color(255, 0, 255)
        //             ->backgroundColor(255, 255, 255)
        //             ->generate('https://google.com');
        //             echo $qrCode;            
    }


    public function sessionData($user)
    {

        // Revoke all previous tokens
        $user->tokens()->delete();// logout in other devices

         // Set the expiration time in minutes //addMinutes()
        //$token_expire_at = Carbon::now()->addSeconds(120); // 120 minutes = 2 hours
        $token_expire_at = Carbon::now()->addMinutes(60); // 120 minutes = 2 hours

        $token  = $user->createToken($user->name,[],$token_expire_at)->plainTextToken;

        $data = [];

        $data['id'] = Encryptor('encrypt',$user['id']);
        $data['type'] = 'NA'; //$user['type'];
        $data['name'] = $user['name'];
        $data['username'] = $user['username'];
        $data['email'] = $user['email'];
        $data['is_logged'] = true; 
        $data['token'] = $token;

        return $data;
    }



    public function resendOtp(Request $request)
    {

        $user_id = User_Id();

        $mobile_no = '';

        $otp = generateOTP();

        $sendOTP = sendOTP($otp,$mobile_no);

        if($sendOTP){

            $update = array();
            $update['otp'] = $otp;

            User::where('id',$user_id)->update($update);

            return response()->json(['status' => true,'message' => 'OTP Sent Successfully.','data' => []]);

        }else{
            return response()->json(['status' => false,'message' => 'OTP Sent Failed.','data' => []]);
        }
        
    }


    public function verifyOtp_and_update_password(Request $request)
    {
        $otp = $request->otp;
        $user_id = User_Id();
        $password =  $request->password;
        $db_otp = 123456;

        $user = User::select('otp')->where('id',$user_id)->get();
  
        $db_otp = $user[0]->otp;

    
        if($otp == $db_otp){

            $update = array();
            $update['password'] = HashPassword($password);

            User::where('id',$user_id)->update($update);

            $this->updatePasswordLogs($password);

            return response()->json(['status' => true,'message' => 'OTP Verify and Password updated Successfully.']);

        }else{

            return response()->json(['status' => false,'message' => 'Please enter the vaild OTP.']);

        }
    }

    public function updatePasswordLogs($password){
        $user_id = User_Id();

        $user = DB::select("SELECT id FROM password_manager WHERE id=$user_id");
        if(!empty($user) && isset($user[0]->id)){
            $update = array();
            $update['password'] = Encryptor('encrypt',$password);
            $update['last_updated'] = DateTime();

            DB::table('password_manager')->where('user_id',$user_id)->update($update);
        }else{

            $save = array();
            $save['user_id'] = $user_id;
            $save['password'] = Encryptor('encrypt',$password);
            $save['last_updated'] = DateTime();
            $save['last_updated'] = DateTime();
            
            DB::table('password_manager')->insert($save);
            
        } 
        return true;
    }
    
   

    public function logout(Request $request){
        
    }
}
