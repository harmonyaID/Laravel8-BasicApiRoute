<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Models\SysResto;
use App\Models\SysRestoMenu;
use App\Models\SysRestoBusinessHours;
use App\Models\SysCustomers;
use App\Models\SysOrders;
use App\Models\SysOrderDetails;
use App\Models\User;
use App\Models\SysToken;
use DateTime;
use File;
use DB;

class SysRestoController extends Controller
{
    public function login(Request $request) {
		$username	= $request->input("username");
        $passwd = $request->input("password");
        $pass = MD5($passwd);
        $now = new DateTime();

        $chkdata = DB::table('users')
                ->where('name','=', $username)
                ->where('password','=', $pass)
                ->where('role','=', 'resto')
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
                                    ->where('user_type', '=', 'resto')
                                    ->get();
    
                if(count($chcktoken) > 0){
                    $user_token = SysToken::find($chcktoken[0]->id);
                    $user_token->token = $generated_token;
                    $user_token->save();
                }else{
                    SysToken::create([
                        'user_id' => $userid,
                        'user_type' => 'resto',
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

    public function getRestoOpenCertainTime(Request $request)
    {
        $token = $request->header('Authorizationtoken');
        $UID = $request->input('user_id');

        if($UID != ""){
            if($token == ""){
                $status       = false;
                $status_code  = 401;
                $message      = "Unauthorized";
                $data         = null;
            }else{
                $userid = $request->input('user_id');
        
                $chcktoken = SysToken::where('user_id', '=', $userid)
                                    ->where('user_type', '=', 'customer')
                                    ->where('token', '=', $token)
                                    ->get();
        
                if(count($chcktoken) > 0){ 
                    $dataResults = SysResto::whereHas('opentime', function (Builder $query) use($request) {
                        $query->where('day_open','=',$request->input('dayopen'))->orwhere('start_time','=',$request->input('timeopen'))->where('end_time','=',$request->input('timeclose'));
                    })->orderBy($request->input('sortby'),$request->input('sort'))->get();
            
                    try
                    {
                        if(count($dataResults) > 0 )
                        {
                            $status       = true;
                            $status_code  = 200;
                            $message      = "data found";
                            $data         = $dataResults;
                        }else {
                            $status       = false;
                            $status_code  = 404;
                            $message      = "data not found";
                            $data         = null;
                        }
                    }
                    catch (\Exception $e)
                    {
                        $status       = false;
                        $status_code  = 501;
                        $message      = $e->getmessage();
                        $data         = null;
                    }
                }else{
                    $status       = false;
                    $status_code  = 401;
                    $message      = "Unauthenticated";
                    $data         = null;
                }
            }
        }else{
            $dataResults = SysResto::whereHas('opentime', function (Builder $query) use($request) {
                $query->where('day_open','=',$request->input('dayopen'))->orwhere('start_time','=',$request->input('timeopen'))->where('end_time','=',$request->input('timeclose'));
            })->orderBy($request->input('sortby'),$request->input('sort'))->get();
    
            try
            {
                if(count($dataResults) > 0 )
                {
                    $status       = true;
                    $status_code  = 200;
                    $message      = "data found";
                    $data         = $dataResults;
                }else {
                    $status       = false;
                    $status_code  = 404;
                    $message      = "data not found";
                    $data         = null;
                }
            }
            catch (\Exception $e)
            {
                $status       = false;
                $status_code  = 501;
                $message      = $e->getmessage();
                $data         = null;
            }
        }

       
        $resp = ['status' => $status,
                'status_code' => $status_code,
                'message' => $message,
                'data'  => $data];

        return response()->json($resp);
    }

    public function getRestoCertainMenu(Request $request)
    {
        $token = $request->header('Authorizationtoken');
        $UID = $request->input('user_id');

        if($UID != ""){
            if($token == ""){
                $status       = false;
                $status_code  = 401;
                $message      = "Unauthorized";
                $data         = null;
            }else{
                $userid = $request->input('user_id');
        
                $chcktoken = SysToken::where('user_id', '=', $userid)
                                    ->where('user_type', '=', 'customer')
                                    ->where('token', '=', $token)
                                    ->get();
        
                if(count($chcktoken) > 0){ 
                    $dataResults = DB::table('resto')
                                ->join('resto_menu','resto_menu.sys_resto_id','resto.id')
                                ->select('resto.id','resto.name','resto.resto_balance','resto.location','resto.status',DB::raw("COUNT(resto_menu.id) as totalMenu"),'resto.created_at','resto.updated_at')
                                ->whereBetween('resto_menu.menu_price', [$request->input('fromprice'), $request->input('toprice')])
                                ->groupBy('resto.id')
                                ->having(DB::raw('count(resto_menu.id)'), '>=', $request->input('number'))
                                ->orderBy('totalMenu',$request->input('sort'))
                                ->get();

                    try
                    {
                        if(count($dataResults) > 0 )
                        {
                            $status       = true;
                            $status_code  = 200;
                            $message      = "data found";
                            $data         = $dataResults;
                        }else {
                            $status       = false;
                            $status_code  = 404;
                            $message      = "data not found";
                            $data         = null;
                        }
                    }
                    catch (\Exception $e)
                    {
                        $status       = false;
                        $status_code  = 501;
                        $message      = $e->getmessage();
                        $data         = null;
                    }
                }else{
                    $status       = false;
                    $status_code  = 401;
                    $message      = "Unauthenticated";
                    $data         = null;
                }
            }
        }
        else{
            $dataResults = DB::table('resto')
                        ->join('resto_menu','resto_menu.sys_resto_id','resto.id')
                        ->select('resto.id','resto.name','resto.resto_balance','resto.location','resto.status',DB::raw("COUNT(resto_menu.id) as totalMenu"),'resto.created_at','resto.updated_at')
                        ->whereBetween('resto_menu.menu_price', [$request->input('fromprice'), $request->input('toprice')])
                        ->groupBy('resto.id')
                        ->having(DB::raw('count(resto_menu.id)'), '>=', $request->input('number'))
                        ->orderBy('totalMenu',$request->input('sort'))
                        ->get();

            try
            {
                if(count($dataResults) > 0 )
                {
                    $status       = true;
                    $status_code  = 200;
                    $message      = "data found";
                    $data         = $dataResults;
                }else {
                    $status       = false;
                    $status_code  = 404;
                    $message      = "data not found";
                    $data         = null;
                }
            }
            catch (\Exception $e)
            {
                $status       = false;
                $status_code  = 501;
                $message      = $e->getmessage();
                $data         = null;
            }
        }
        
        $resp = ['status' => $status,
                'status_code' => $status_code,
                'message' => $message,
                'data'  => $data];

        return response()->json($resp);
    }

    public function getRestoByLocationUser(Request $request)
    {
        $token = $request->header('Authorizationtoken');

        if($token == ""){
            $status       = false;
            $status_code  = 401;
            $message      = "Unauthorized";
            $data         = null;
        }else{
            $userid = $request->input('user_id');
            $chcktoken = SysToken::where('user_id', '=', $userid)
                                ->where('user_type', '=', 'customer')
                                ->where('token', '=', $token)
                                ->get();
    
            if(count($chcktoken) > 0){ 
                $UID = $request->input('user_id');
                $locations = $request->input('location');
                $radius = $request->input('radius');
                $dataLoc = explode(',',$locations);
                $latitude = $dataLoc[0];
                $longitude = $dataLoc[1];
                
                $dataResults = SysResto::selectRaw("id, name, location, resto_balance, status, created_at , 
                                ( 6371 * acos( cos( radians(?) ) *
                                cos( radians( latitude ) )
                                * cos( radians( longitude ) - radians(?)
                                ) + sin( radians(?) ) *
                                sin( radians( latitude ) ) )
                                ) AS distance", [$latitude, $longitude, $latitude])
                    ->having("distance", "<", $radius)
                    ->orderBy("distance",$request->input('sort'))
                    ->get();
        
                try
                {
                    if(count($dataResults) > 0 )
                    {
                        $status       = true;
                        $status_code  = 200;
                        $message      = "data found";
                        $data         = $dataResults;
                    }else {
                        $status       = false;
                        $status_code  = 404;
                        $message      = "data not found";
                        $data         = null;
                    }
                }
                catch (\Exception $e)
                {
                    $status       = false;
                    $status_code  = 501;
                    $message      = $e->getmessage();
                    $data         = null;
                }
            }else{
                $status       = false;
                $status_code  = 401;
                $message      = "Unauthenticated";
                $data         = null;
            }
        }

        $resp = ['status' => $status,
                'status_code' => $status_code,
                'message' => $message,
                'data'  => $data];

        return response()->json($resp);
    }

    public function getRestoPopular(Request $request)
    {
        $token = $request->header('Authorizationtoken');
        $UID = $request->input('user_id');

        if($UID != ""){
            if($token == ""){
                $status       = false;
                $status_code  = 401;
                $message      = "Unauthorized";
                $data         = null;
            }else{
                $userid = $request->input('user_id');
        
                $chcktoken = SysToken::where('user_id', '=', $userid)
                                    ->where('user_type', '=', 'customer')
                                    ->where('token', '=', $token)
                                    ->get();
        
                if(count($chcktoken) > 0){ 
                    if($request->input('filter') == 'amount')
                    {
                        $dataResults = DB::table('orders')
                                    ->join('orders_details','orders_details.sys_orders_id','orders.id')
                                    ->select('orders_details.resto_name',DB::raw("SUM(orders_details.price) as amounttrx"))
                                    ->groupBy('orders_details.resto_name')
                                    ->orderBy('amounttrx',$request->input('sort'))
                                    ->get();
                    }
                    else
                    {
                        $dataResults = DB::table('orders')
                                    ->join('orders_details','orders_details.sys_orders_id','orders.id')
                                    ->select('orders_details.resto_name',DB::raw("COUNT(orders_details.resto_name) as totaltrx"))
                                    ->groupBy('orders_details.resto_name')
                                    ->orderBy('totaltrx',$request->input('sort'))
                                    ->get();
                    }
            
                    try
                    {
                        if(count($dataResults) > 0 )
                        {
                            $status       = true;
                            $status_code  = 200;
                            $message      = "data found";
                            $data         = $dataResults;
                        }else {
                            $status       = false;
                            $status_code  = 404;
                            $message      = "data not found";
                            $data         = null;
                        }
                    }
                    catch (\Exception $e)
                    {
                        $status       = false;
                        $status_code  = 501;
                        $message      = $e->getmessage();
                        $data         = null;
                    }
                }else{
                    $status       = false;
                    $status_code  = 401;
                    $message      = "Unauthenticated";
                    $data         = null;
                }
            }
        }
        else{
            if($request->input('filter') == 'amount')
            {
                $dataResults = DB::table('orders')
                            ->join('orders_details','orders_details.sys_orders_id','orders.id')
                            ->select('orders_details.resto_name',DB::raw("SUM(orders_details.price) as amounttrx"))
                            ->groupBy('orders_details.resto_name')
                            ->orderBy('amounttrx',$request->input('sort'))
                            ->get();
            }
            else
            {
                $dataResults = DB::table('orders')
                            ->join('orders_details','orders_details.sys_orders_id','orders.id')
                            ->select('orders_details.resto_name',DB::raw("COUNT(orders_details.resto_name) as totaltrx"))
                            ->groupBy('orders_details.resto_name')
                            ->orderBy('totaltrx',$request->input('sort'))
                            ->get();
            }
    
            try
            {
                if(count($dataResults) > 0 )
                {
                    $status       = true;
                    $status_code  = 200;
                    $message      = "data found";
                    $data         = $dataResults;
                }else {
                    $status       = false;
                    $status_code  = 404;
                    $message      = "data not found";
                    $data         = null;
                }
            }
            catch (\Exception $e)
            {
                $status       = false;
                $status_code  = 501;
                $message      = $e->getmessage();
                $data         = null;
            }
        }


        $resp = ['status' => $status,
                'status_code' => $status_code,
                'message' => $message,
                'data'  => $data];

        return response()->json($resp);
    }

    public function getSearchData(Request $request)
    {

        $token = $request->header('Authorizationtoken');
        $UID = $request->input('user_id');

        if($UID != ""){
            if($token == ""){
                $status       = false;
                $status_code  = 401;
                $message      = "Unauthorized";
                $data         = null;
            }else{
                $userid = $request->input('user_id');
        
                $chcktoken = SysToken::where('user_id', '=', $userid)
                                    ->where('user_type', '=', 'customer')
                                    ->where('token', '=', $token)
                                    ->get();
        
                if(count($chcktoken) > 0){ 
                    if($request->input('type') == 'resto')
                    {
                        $dataResults = SysResto::where('name', 'like', '%'.$request->input('keyword').'%')->orderBy($request->input('sortby'),$request->input('sort'))->get();
                    }
                    elseif ($request->input('type') == 'menu') {
                        $dataResults = SysResto::whereHas('menu', function (Builder $query) use($request) {
                            $query->where('name', 'like', '%'.$request->input('keyword').'%');
                        })->orderBy($request->input('sortby'),$request->input('sort'))->get();
                    }
                    else
                    {
                        $resultresto = SysResto::where('name', 'like', '%'.$request->input('keyword').'%')->orderBy($request->input('sortby'),$request->input('sort'))->get();
                        $resultmenu = SysRestoMenu::where('name', 'like', '%'.$request->input('keyword').'%')->orderBy($request->input('sortby'),$request->input('sort'))->get();
                        $dataResults = ['resto' => $resultresto,
                                        'menu' => $resultmenu];
                    }
                
                    try
                    {
                        if(count($dataResults) > 0 )
                        {
                            $status       = true;
                            $status_code  = 200;
                            $message      = "data found";
                            $data         = $dataResults;
                        }else {
                            $status       = false;
                            $status_code  = 404;
                            $message      = "data not found";
                            $data         = null;
                        }
                    }
                    catch (\Exception $e)
                    {
                        $status       = false;
                        $status_code  = 501;
                        $message      = $e->getmessage();
                        $data         = null;
                    }
                }else{
                    $status       = false;
                    $status_code  = 401;
                    $message      = "Unauthenticated";
                    $data         = null;
                }
            }
        }
        else{
            if($request->input('type') == 'resto')
            {
                $dataResults = SysResto::where('name', 'like', '%'.$request->input('keyword').'%')->orderBy($request->input('sortby'),$request->input('sort'))->get();
            }
            elseif ($request->input('type') == 'menu') {
                $dataResults = SysResto::whereHas('menu', function (Builder $query) use($request) {
                    $query->where('name', 'like', '%'.$request->input('keyword').'%');
                })->orderBy($request->input('sortby'),$request->input('sort'))->get();
            }
            else
            {
                $resultresto = SysResto::where('name', 'like', '%'.$request->input('keyword').'%')->orderBy($request->input('sortby'),$request->input('sort'))->get();
                $resultmenu = SysRestoMenu::where('name', 'like', '%'.$request->input('keyword').'%')->orderBy($request->input('sortby'),$request->input('sort'))->get();
                $dataResults = ['resto' => $resultresto,
                                'menu' => $resultmenu];
            }
        
            try
            {
                if(count($dataResults) > 0 )
                {
                    $status       = true;
                    $status_code  = 200;
                    $message      = "data found";
                    $data         = $dataResults;
                }else {
                    $status       = false;
                    $status_code  = 404;
                    $message      = "data not found";
                    $data         = null;
                }
            }
            catch (\Exception $e)
            {
                $status       = false;
                $status_code  = 501;
                $message      = $e->getmessage();
                $data         = null;
            }
        }

        $resp = ['status' => $status,
                'status_code' => $status_code,
                'message' => $message,
                'data'  => $data];

        return response()->json($resp);
    }
}
