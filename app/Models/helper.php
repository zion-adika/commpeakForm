<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class helper
{
    public $customerCallsArr = [];
    public $geoArr = [];
    public $continentIps = [];
    public $calculationCalls = [];

    public function __construct(Request $request)
    {
        calls::truncate();
        $this->customerCallsArr = $this->getCsvArray($request);
        $this->initArrays();

    }

    private function getCsvArray(Request $request)
    {
        if ($files = $request->file('file')) {
            $path = $request->file('file')->getRealPath();
            $data = array_map('str_getcsv', file($path));
            return $data;
        }
    }

    private function initArrays()
    {
        $content = file('http://download.geonames.org/export/dump/countryInfo.txt');
        foreach($content as $line)
        {
            if(substr($line,0,4) == "#ISO")
            {
                $headerArr = explode("\t",$line);

            }
            if (substr($line,0,1) != "#")
            {
                $lilneArr = explode("\t",$line);
                $phoneIndex = array_search('Phone', $headerArr);
                $continentIndex = array_search('Continent', $headerArr);
                $countryIndex = array_search('Country', $headerArr);
                $this->geoArr[$lilneArr[$continentIndex]][] = $lilneArr[$phoneIndex];

            }
        }
        foreach ($this->customerCallsArr as $customerCall)
        {
            $customer_id = $customerCall[0];
            $duration_call = $customerCall[2];
            $phone_number = $customerCall[3];
            $ip_customer = $customerCall[4];

            $this->ipstackApi($ip_customer);

        }
    }
    private function ipstackApi($ip)
    {
        if (isset($this->continentIps[$ip]))
        {
            if ($this->continentIps[$ip] !== false){
                return ['error' => false,'continent_code' => $this->continentIps[$ip]];
            }else{
                return ['error' => true,'continent_code' => ''];
            }

        }else{
            $access_key = env("ACCESS_KEY_IPSTCK", "");
            $ch = curl_init('http://api.ipstack.com/'.$ip.'?access_key='.$access_key.'');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($ch);
            curl_close($ch);

            $api_result = json_decode($json, true);

            if (!isset($api_result['success']))
            {
                $this->continentIps[$ip] = $api_result['continent_code'];

            }else{
                $this->continentIps[$ip] = false;

            }
        }
    }

    public function arrysCheck($phone_number,$ip_customer)
    {
        foreach ($this->geoArr as $continent => $phones)
        {
            $indexval = array_search(substr($phone_number,0,-9), $phones);

            if ($indexval !== false)
            {
                if (isset($this->continentIps[$ip_customer]) && $this->continentIps[$ip_customer]!== false)
                {

                    if ($this->continentIps[$ip_customer] == $continent)
                    {
                        return ['action'=>'==','phone'=>$phones[$indexval],'continent_code'=>$continent];
                    }elseif ($this->continentIps[$ip_customer] != $continent)
                    {
                        return ['action'=>'!=','phone'=>$phones[$indexval],'continent_code'=>$continent];
                    }else{
                        return ['action'=>'empty','phone'=>'','continent_code'=>''];
                    }
                }
                return ['action'=>'empty','phone'=>'','continent_code'=>''];
            }
        }
        return ['action'=>'empty','phone'=>'','continent_code'=>''];
    }

    public function insertOrCreate($customer_id,$total_duration = null,$total_duration_continent = null)
    {
        $get_user_id = calls::where('customer_id', $customer_id)->count(); //laravel returns an integer

        if($get_user_id == 0) {

            $customer_call = new calls();
            $customer_call->customer_id = $customer_id;
            if ($total_duration > 0)
            {
                $customer_call->total_calls = 1;
                $customer_call->total_duration = $total_duration;
                if($total_duration_continent > 0)
                {
                    $customer_call->num_calls_contiinent = 1;
                    $customer_call->total_duration_continent = $total_duration_continent;
                }
            }

            $customer_call->save();
        } else {

            $row = calls::where('customer_id', $customer_id)->get();
            $current_totall_calls = $row[0]->total_calls;
            $current_total_duration = $row[0]->total_duration;
            $current_totall_calls_continent = $row[0]->num_calls_contiinent;
            $current_total_duration_continent = $row[0]->total_duration_continent;

            $calc_totall_calls = $current_totall_calls+1;
            $calc_total_duration = $current_total_duration+$total_duration;

            if($total_duration_continent > 0)
            {
                $calc_num_calls_contiinent = $current_totall_calls_continent+1;
                $calc_total_duration_continent = $current_total_duration_continent+$total_duration;

                $affectedRows = calls::where('customer_id', $customer_id)->update(array('total_calls' => $calc_totall_calls,
                    'total_duration'=> $calc_total_duration,
                    'num_calls_contiinent'=> $calc_num_calls_contiinent,
                    'total_duration_continent'=> $calc_total_duration_continent));

            }elseif ($total_duration > 0){

                $affectedRows = calls::where('customer_id', $customer_id)->update(array('total_calls' => $calc_totall_calls,
                    'total_duration'=> $calc_total_duration));
            }

        }
    }
}
