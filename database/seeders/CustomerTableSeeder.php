<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SysRestoMenu;
use App\Models\SysRestoBusinessHours;
use App\Models\SysResto;
use App\Models\SysCustomers;
use App\Models\SysOrders;
use App\Models\SysOrderDetails;
use App\Models\User;
use File;

class CustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $jsonString = [];
        if(File::exists(base_path('storage/app/users.json'))){
            $jsonString = file_get_contents(base_path('storage/app/users.json'));
            $jsonString = json_decode($jsonString, true);
        }

        foreach ($jsonString as $key => $value) {

            $idUser = User::create(array(
                'name' => strtolower(str_replace(' ','',$value['name'])),
                'email' => strtolower(str_replace(' ','',$value['name'])).$key.'@mail.com',
                'password' => MD5(ENV('PASSAPP')),
                'role' => 'customer',
                'status' => 1,
            ))->id;

            $ids = SysCustomers::create(array(
                'user_id' =>  $idUser,
                'fullname' => $value['name'],
                'balance' => $value['balance'],
                'location' => $value['location'],
                'status' => 1,
            ))->id;

            $totals = 0.00;
            foreach ($jsonString[$key]['purchases'] as $itemprice) {
                $price = (float)$itemprice['amount'];
                $totals = $totals + $price;
            }
            $trxID = SysOrders::create(array(
                'sys_customers_id' => $ids,
                'fullname' => $value['name'],
                'total' => $totals,
                'status' => 1,
            ))->id;

            foreach ($jsonString[$key]['purchases'] as $item) {
                $s = $item['date'];
                $date = strtotime($s);
                $fulldate = date('Y-m-d H:i:s', $date);

                SysOrderDetails::create(array(
                    'sys_orders_id' =>  $trxID,
                    'resto_name' => $item['restaurant_name'],
                    'menu_name' => $item['dish'],
                    'price' => $item['amount'],
                    'date_order' => $fulldate,
                ));
            }
        }
    }
}
