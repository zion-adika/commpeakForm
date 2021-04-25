<?php

namespace App\Http\Controllers;

use App\Models\calls;
use Illuminate\Http\Request;

class CallsController extends Controller
{
    public $geoArr = [];
    public $continentIps = [];
    public $calculationCalls = [];

    public function index()
    {
        return view('callsViiew');
    }

    public function store(Request $request)
    {

        request()->validate([
            'file'  => 'required|mimes:doc,docx,pdf,txt|max:2048',
        ]);

        $customerCallsArr = $this->getCsvArray($request);

        $this->geonamesApiInit();

        foreach ($customerCallsArr as $customerCall)
        {
            $customer_id = $customerCall[0];
            $duration_call = $customerCall[2];
            $phone_number = $customerCall[3];
            $ip_customer = $customerCall[4];

            $continent_from_ip = $this->ipstackApi($ip_customer);
            $continent_from_phone = $this->geonamesArrCheck($phone_number);

            if (!$continent_from_ip['error'] && $continent_from_ip['continent_code'] == $continent_from_phone['continent_code'])
            {

                $this->insertOrCreate($customer_id,$duration_call,$duration_call);

            }elseif(!$continent_from_ip['error'] && $continent_from_ip['continent_code'] != $continent_from_phone['continent_code'])
            {
                $this->insertOrCreate($customer_id,$duration_call);

            }elseif($continent_from_ip['error'])
            {
                $this->insertOrCreate($customer_id);

            }

            if($continent_from_phone['continent_code'] == '' && $continent_from_phone['phone'] == '')
            {
                $this->insertOrCreate($customer_id);

            }

        }

        $results = calls::all();
        $result_html = '<table class="table table-dark">
                          <thead>
                            <tr>
                              <th scope="col">Customer Id</th>
                              <th scope="col">Number Calls - same continent</th>
                              <th scope="col">Total Duration Calls - same continent </th>
                              <th scope="col">Number All Calls</th>
                              <th scope="col">Total Duration All Calls</th>
                            </tr>
                          </thead>
                          <tbody>
                      ';

        foreach ($results as $row_table)
        {
            $result_html.='<tr>
                              <th scope="row">'.$row_table->customer_id.'</th>
                              <td>'.$row_table->num_calls_contiinent.'</td>
                              <td>'.$row_table->total_duration_continent.'</td>
                              <td>'.$row_table->total_calls.'</td>
                              <td>'.$row_table->total_duration.'</td>
                            </tr>';
        }
        $result_html.='</tbody>
                        </table>';
            return Response()->json([
                "success" => true,
                "table" => $result_html
            ]);

    }

    public function insertOrCreate($customer_id,$total_duration = null,$total_duration_continent = null)
    {
        $get_user_id = calls::where('customer_id', $customer_id)->count(); //laravel returns an integer

        if($get_user_id == 0) {

            $customer_call = new calls();
            $customer_call->customer_id = $customer_id;
            if ($total_duration != null)
            {

                $customer_call->total_calls = 1;
                $customer_call->total_duration = $total_duration;
                if($total_duration_continent!= null)
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
            if($total_duration_continent!= null)
            {
                $affectedRows = calls::where('customer_id', $customer_id)->update(array('total_calls' => $current_totall_calls+1,
                                                                                        'total_duration'=> $current_total_duration+$total_duration,
                                                                                        'num_calls_contiinent'=> $current_totall_calls_continent+1,
                                                                                        'total_duration_continent'=> $current_total_duration_continent+$total_duration));
            }elseif ($total_duration != null){

                $affectedRows = calls::where('customer_id', $customer_id)->update(array('total_calls' => $current_totall_calls+1,
                                                                                        'total_duration'=> $current_total_duration+$total_duration));
            }


        }
    }

    public function getCsvArray(Request $request)
    {
        if ($files = $request->file('file')) {
            $path = $request->file('file')->getRealPath();
            $data = array_map('str_getcsv', file($path));
            return $data;
        }
    }

    public function ipstackApi($ip)
    {

        if (isset($this->continentIps[$ip]))
        {
            if ($this->continentIps[$ip] !== false){
                return ['error' => false,'continent_code' => $this->continentIps[$ip]];
            }else{
                return ['error' => true,'continent_code' => ''];
            }

        }else{
            $access_key = 'ed09e98ccc0c3f163c4d575a764f3629';
            $ch = curl_init('http://api.ipstack.com/'.$ip.'?access_key='.$access_key.'');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $json = curl_exec($ch);
            curl_close($ch);

            $api_result = json_decode($json, true);

            if (!isset($api_result['success']))
            {
                $this->continentIps[$ip] = $api_result['continent_code'];
                return ['error' => false,'continent_code' => $api_result['continent_code'],'continent_name' => $api_result['continent_name']];


            }else{
                $this->continentIps[$ip] = false;
                return ['error' => true,'error_code' => $api_result['code'],'error_type'=> $api_result['type'],'error_info'=> $api_result['info']];
            }
        }
    }
    public function geonamesApiInit()
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
    }
    public function geonamesArrCheck($phone_number)
    {
        foreach ($this->geoArr as $continent => $phones)
        {
            $indexval = array_search(substr($phone_number,0,-9), $phones);

            if ($indexval !== false)
            {
                return ['phone'=>$phones[$indexval],'continent_code'=>$continent];

            }
        }
        return ['phone'=>'','continent_code'=>''];
    }
}
