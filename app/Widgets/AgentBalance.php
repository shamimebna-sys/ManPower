<?php

namespace App\Widgets;

use Illuminate\Support\Facades\Auth;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Widgets\BaseDimmer;

class AgentBalance extends BaseDimmer
{

    public function run()
    {
        $table = [
            'table_title' => 'AGENT BALANCE',
            'table_th' => ['AGENT NAME', 'BALANCE'],
            'table_data' => [
                /* ['John Doe', 'john@example.com', '1234567890', 'Admin'],
                 ['Jane Smith', 'jane@example.com', '9876543210', 'Manager'],
                 ['Bob Ray', 'bob@example.com', '111222333', 'User'],*/
            ],
        ];

        $query = "select name, balance
    from agents
    where status  = 'A'
    and name not like '%test%'
    order by agents.balance desc limit 5
";
        $table['table_data'] = (array) \DB::select($query);

        return view('voyager::widgets.table-dim', [
            'table_title' => $table['table_title'],
            'table_th'    => $table['table_th'],
            'table_data'  => $table['table_data'],
        ]);
    }

    public function shouldBeDisplayed()
    {
        /*
           1	 	Super Admin
           2	 	Administrator
           100	 	Normal User
           101	 	Agent
           102	 	Candidate
           103	 	Teacher
           104	 	Employee
           105	 	Employer
           106	 	owner
           107	 	Company
           108	 	Agency
       */
        if( in_array(\auth()->user()->role_id, [1, 2, 106])){
            return true;
        }else{
            return false;
        }
    }
}
