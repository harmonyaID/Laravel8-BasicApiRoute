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

class SysOrderController extends Controller
{
    public function orderByUsers(Request $request)
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
                $cust = SysCustomers::where('user_id','=',$userid)->first();
                $dataResults = SysOrders::with('item')->where('sys_customers_id','=',$cust->id)->get(); // can be optimized by query builder or DB RAW
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

    public function orderByResto(Request $request)
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
                                ->where('user_type', '=', 'resto')
                                ->where('token', '=', $token)
                                ->get();
    
            if(count($chcktoken) > 0){ 
                $id = $request->input('user_id');
                $dataResto =  SysResto::where('user_id',$id)->first();
                $dataResults = DB::table('orders')
                            ->join('orders_details','orders_details.sys_orders_id','orders.id')
                            ->select('orders.*','orders_details.*')
                            ->where('orders_details.resto_name','=',$dataResto->name)
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

    public function totalUsersOrder(Request $request)
    {
        $id = $request->input('user_id');
        $dataRes = SysResto::where('id','=',$id)->first();
        if($request->input('position') == 'below')
        {
            $dataTrx = DB::table('customers')
                        ->join('orders','orders.sys_customers_id','customers.id')
                        ->join('orders_details','orders_details.sys_orders_id','orders.id')
                        ->select('customers.id')
                        ->whereBetween('orders_details.date_order', [$request->input('from_date'), $request->input('to_date')])
                        ->where('orders.total','<=',$request->input('amount'))
                        ->where('orders_details.resto_name','=',$dataRes->name)
                        ->groupBy('orders.id')->get();
        }
        else
        {
            $dataTrx = DB::table('customers')
                        ->join('orders','orders.sys_customers_id','customers.id')
                        ->join('orders_details','orders_details.sys_orders_id','orders.id')
                        ->select('customers.id')
                        ->whereBetween('orders_details.date_order', [$request->input('from_date'), $request->input('to_date')])
                        ->where('orders.total','>=',$request->input('amount'))
                        ->where('orders_details.resto_name','=',$dataRes->name)
                        ->groupBy('orders.id')->get();
        }
       
        $dataResults = ['totalnumber' => count($dataTrx)];

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
       
        $resp = ['status' => $status,
                'status_code' => $status_code,
                'message' => $message,
                'data'  => $data];

        return response()->json($resp);
    }

    public function getTopUsersTransaction(Request $request)
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
                                ->where('user_type', '=', 'resto')
                                ->where('token', '=', $token)
                                ->get();
    
            if(count($chcktoken) > 0){ 
                $dataResults = DB::table('customers')
                        ->join('orders','orders.sys_customers_id','customers.id')
                        ->join('orders_details','orders_details.sys_orders_id','orders.id')
                        ->select('customers.id','customers.fullname','customers.balance','customers.location','customers.status','orders.id as orderID',DB::raw("SUM(orders_details.price) as AmountItemsOrdered"),'customers.created_at','customers.updated_at')
                        ->whereBetween('orders_details.date_order', [$request->input('from_date'), $request->input('to_date')])
                        ->groupBy('orders.id')
                        ->orderBy('AmountItemsOrdered',$request->input('sort'))
                        ->limit($request->input('limit'))->get();

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

    public function storeOrders(Request $request)
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
    
            if(count($chcktoken) > 0)
            { 
                $user_id = $request->input("user_id");
                $itemorders = $request->input("items");
                $now            = new DateTime();
                $status         = 1;

                $User = SysCustomers::where('user_id','=',$user_id)->first();
                $orderDetails = json_decode($itemorders, true);
                $total = 0;

                foreach($orderDetails['orderlist'] as $obj)
                {
                    $total = $total+$obj['price'];
                }
                            
                $sysOrder = SysOrders::create([
                    'sys_customers_id' => $User->id,
                    'fullname' => $User->fullname,
                    'total' => $total,
                    'status' => 1,
                ]);
                DB::commit();

                foreach($orderDetails['orderlist'] as $obj)
                {
                    SysOrderDetails::create([
                        'sys_orders_id' => $sysOrder->id,
                        'resto_name' => $obj['resto'],
                        'menu_name' => $obj['menu'],
                        'price' => $obj['price'],
                    ]);
                    DB::commit();

                    $rest = SysResto::where('name','=',$obj['resto'])->first();
                    $restbalance = floatval($rest->resto_balance);
                    $resttotal = $restbalance+floatval($obj['price']);

                    DB::table('resto')
                        ->where('name', $obj['resto'])
                        ->update([
                            'resto_balance' => $resttotal,
                    ]);

                    DB::commit();
                }

                $dataResults = array(
                    'user_id'	=> $user_id,
                    'customer_name'	=> $User->fullname,
                    'order_id'	=> $sysOrder->id,
                    'total'	=> $total,
                    'status' => $status,
                    'created_at' => new DateTime(),
                    'updated_at' => new DateTime(),
                );

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
}
