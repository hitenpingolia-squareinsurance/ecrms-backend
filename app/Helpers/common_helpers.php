<?php

if (!function_exists('HashPassword')) {

    function HashPassword($password){
        error_reporting(0);
        $options = [
                'salt' => '4Oj>;uW`G!#[5HX3<ux_vj`>g:gL-4F7|tuz?l%aT)t`/Kb$*/?rkHPZO,u`gD)c', //write your own code to generate a suitable salt
                'cost' => 12 // the default cost is 10
            ];
            $hash = password_hash($password, PASSWORD_DEFAULT, $options);
            return $hash;
    }

}


if (!function_exists('VerifyPassword')) {

   function VerifyPassword($password,$hash){
       return password_verify($password,$hash);
   }

}

if (!function_exists('Encryptor')) {

    function Encryptor($action, $string){

        $output = false;

        $encrypt_method = "AES-256-CBC";

        //pls set your unique hashing key

        // $secret_key = env(Encryptor_Secret_Key);
        // $secret_iv = env(Encryptor_Secret_Iv);

        $secret_key = 'BMSVkuld%:bTXz,3r>6|FW#!7eSs>vM~n+48~{Mh$#A4p).)#wV3^_y-B.6WCar=b4.';
        $secret_iv = '3w8XD|r@n:nxp|oml]nw$-KEc|rT$H).(~ &`gnV!vD0vs|?r]#Zdr-qRlOV@&#6';
 
        // hash

        $key = hash('sha256', $secret_key);


        // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning

        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        //do the encyption given text/string/number

        if( $action == 'encrypt' ) {

            $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
            $output = base64_encode($output);
        }

        else if( $action == 'decrypt' ){
            //decrypt the given text/string/number
            $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
        }

        return $output;

    }

}


if (!function_exists('DateTime')) {

    function DateTime(){
        return date('Y-m-d H:i:s');
    }
 
 }

 if (!function_exists('getExcelColumnCommaSeprated')) {

    function getExcelColumnCommaSeprated($ar){
        $columns=[];
        if(isset($ar) && !empty($ar)){
            foreach($ar as $col){ 
                if($col['is_checked']==true){
                    $columns[] = $col['label'];
                } 
            }
            return implode(',', $columns);
        }else{
            return '';
        }
    }
 
 }

 

 if (!function_exists('generateOTP')) {

    function generateOTP(){
        $OTP = 123456; //substr(rand(9999,99999999),0,6);
        return $OTP;
    }
 
 }


 if (!function_exists('User_Id')) {

    function User_Id(){
        $enc_user_id = (isset($_POST['user_id']) && !empty($_POST['user_id']))?$_POST['user_id']:$_GET['user_id'];
        $user_id = Encryptor('decrypt',$enc_user_id);
        return $user_id;
    }
 
 }

 if (!function_exists('sendOTP')) {

    function sendOTP($otp,$mobile_no){
        //third party curl/services

        return true;
    }
 
 }

 