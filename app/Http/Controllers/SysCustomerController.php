<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SysToken;
use DB;
use DateTime;
use Image;

class SysCustomerController extends Controller
{
    public function login(Request $request) {
		$username	= $request->input("username");
        $passwd = $request->input("password");
        $pass = MD5($passwd);
        $now = new DateTime();

        $chkdata = DB::table('users')
                ->where('name','=', $username)
                ->where('password','=', $pass)
                ->where('role','=', 'customer')
                ->get();

        if(count($chkdata) > 0)
        {
            if($chkdata[0]->status == 1){
                $userid = $chkdata[0]->id;
                $fullname = $chkdata[0]->name;
                $email = $chkdata[0]->email;
    
                function generateRandomString($length = 20) {
                       $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                       $charactersLength = strlen($characters);
                       $randomString = '';
                       for ($i = 0; $i < $length; $i++) {
                           $randomString .= $characters[rand(0, $charactersLength - 1)];
                       }
                       return $randomString;
                }
                
                $tokn = generateRandomString(20);
                $secret = 'harmonydshinestudio2020';
                $generated_token = hash_hmac('sha256', $tokn, $secret);
                    
                $chcktoken = SysToken::where('user_id', '=', $userid)
                                    ->where('user_type', '=', 'customer')
                                    ->get();
    
                if(count($chcktoken) > 0){
                    $user_token = SysToken::find($chcktoken[0]->id);
                    $user_token->token = $generated_token;
                    $user_token->save();
                }else{
                    SysToken::create([
                        'user_id' => $userid,
                        'user_type' => 'customer',
                        'token' => $generated_token
                    ]);
                    DB::commit();
                }
    
                $retdata = array(
                    'user_id'	=> $userid,
                    'name' => $fullname,
                    'email' => $email,
                    'token' =>  $generated_token
                );
    
                $status       = true;
                $status_code  = 200;
                $message      = "data found";
                $data         = $retdata;
            }else{
    
                $status       = false;
                $status_code  = 400;
                $message      = "Account banned. Please contact administrator";
                $data         = null;

            }
        }
        else
        {
            $status       = false;
            $status_code  = 401;
            $message      = "Invalid username or password";
            $data         = null;
        }

        $resp = ['status' => $status,
                'status_code' => $status_code,
                'message' => $message,
                'data'  => $data
        ];

        return response()->json($resp);
    }
}
