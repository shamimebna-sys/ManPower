<?php

namespace App\Widgets;

use Illuminate\Support\Facades\Auth;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Widgets\BaseDimmer;

class Announcement extends BaseDimmer
{

    public function run()
    {
        $table = [
            'table_title' => 'ANNOUNCEMENT',
            'table_th' => ['TOPIC', 'DATE'],
            'table_data' => [
                /* ['John Doe', 'john@example.com', '1234567890', 'Admin'],*/
            ],
        ];

        $query = "select topic, anounce_date
from announcements
where anounce_date  <=CURDATE()
and anounce_date_end  >=CURDATE()
and status = 'A'
 limit 5
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
        if( in_array(\auth()->user()->role_id, [1, 2, 100, 101, 102, 103, 104, 105, 106, 107, 108])){
            return true;
        }else{
            return false;
        }
    }
}
