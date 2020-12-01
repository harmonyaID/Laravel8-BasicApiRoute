<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SysRestoMenu;
use App\Models\SysRestoBusinessHours;
use App\Models\SysResto;
use App\Models\SysCustomers;
use App\Models\User;
use File;

class RestoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $jsonString = [];
        if(File::exists(base_path('storage/app/restaurants.json'))){
            $jsonString = file_get_contents(base_path('storage/app/restaurants.json'));
            $jsonString = json_decode($jsonString, true);
        }

        foreach ($jsonString as $key => $value) {

            $idUser = User::create(array(
                'name' => strtolower(str_replace(' ','',$value['name'])),
                'email' => strtolower(str_replace(' ','',$value['name'])).$key.'@mail.com',
                'password' => MD5('hungry12345678'),
                'role' => 'resto',
                'status' => 1,
            ))->id;

            $dataLoc = explode(',',$value['location']);
            $latitude = $dataLoc[0];
            $longitude = $dataLoc[1];

            $ids = SysResto::create(array(
                'user_id' =>  $idUser,
                'name' => $value['name'],
                'resto_balance' => $value['balance'],
                'location' => $value['location'],
                'latitude' => $latitude,
                'longitude' => $longitude,
                'status' => 1,
            ))->id;

            if($jsonString[$key]['business_hours'] != ''){
                $data_hours = explode('|',$jsonString[$key]['business_hours']);
                foreach($data_hours as $hours){
                    $day = strstr(trim($hours),':', true);
                    $days = explode(',',$day);
                    $time = strstr(trim($hours),':');
                    $time = substr(trim($time),1);
                    $time = trim($time);
                    $open_time = strstr(trim($time),'-', true);
                    $open_time = trim($open_time);
                    $closed_time = strstr(trim($time),'-');
                    $closed_time = trim($closed_time);
                    $closed_time = trim(substr($closed_time,1));
                    foreach($days as $day){
                        if(trim($day) == 'Sun'){
                            $dayOpen = 'Sunday';
                        }elseif (trim($day) == 'Mon') {
                            $dayOpen = 'Monday';
                        }elseif (trim($day) == 'Tue') {
                            $dayOpen = 'Tuesday';
                        }elseif (trim($day) == 'Wed') {
                            $dayOpen = 'Wednesday';
                        }elseif (trim($day) == 'Thu') {
                            $dayOpen = 'Thursday';
                        }elseif (trim($day) == 'Sat') {
                            $dayOpen = 'Saturday';
                        }elseif (trim($day) == 'Fri') {
                            $dayOpen = 'Friday';
                        }elseif (trim($day) == 'Weds') {
                            $dayOpen = 'Wednesday';
                        }elseif (trim($day) == 'Thurs') {
                            $dayOpen = 'Thursday';
                        }else{
                            $dayOpen = trim($day);
                        }
                        SysRestoBusinessHours::create(array(
                            'sys_resto_id' =>  $ids,
                            'day_open' => $dayOpen,
                            'start_time' => trim($open_time),
                            'end_time' => trim($closed_time),
                            'status' => 1,
                        ));
                    }
                }
            }

            foreach ($jsonString[$key]['menu'] as $menu) {
                SysRestoMenu::create(array(
                    'sys_resto_id' =>  $ids,
                    'name' => $menu['name'],
                    'menu_price' => $menu['price'],
                    'status' => 1,
                ));
            }
        }
    }
}
