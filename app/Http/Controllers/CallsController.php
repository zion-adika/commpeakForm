<?php

namespace App\Http\Controllers;

use App\Models\calls;
use App\Models\helper;
use Illuminate\Http\Request;

class CallsController extends Controller
{

    public function index()
    {
        return view('callsViiew');
    }

    public function store(Request $request)
    {

        request()->validate([
            'file'  => 'required|mimes:doc,docx,pdf,txt|max:2048',
        ]);

        $helper = new helper($request);

        foreach ($helper->customerCallsArr as $customerCall)
        {
            $customer_id = $customerCall[0];
            $duration_call = $customerCall[2];
            $phone_number = $customerCall[3];
            $ip_customer = $customerCall[4];
            $customer_continent_check = $helper->arrysCheck($phone_number,$ip_customer);

            switch ($customer_continent_check['action'])
            {
                case '==':
                    $helper->insertOrCreate($customer_id,$duration_call,$duration_call);
                    break;
                case '!=':
                    $helper->insertOrCreate($customer_id,$duration_call);
                    break;
                case 'empty':
                    $this->insertOrCreate($customer_id);
                    break;
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
}
