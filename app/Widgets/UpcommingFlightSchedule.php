<?php

namespace App\Widgets;

use Illuminate\Support\Facades\Auth;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Widgets\BaseDimmer;

class UpcommingFlightSchedule extends BaseDimmer
{

    public function run()
    {
        $table = [
            'table_title' => 'UPCOMMING FLIGHT SCHEDULE',
            'table_th' => ['CANDIDATE',  'PASSPORT', 'DATE', 'TIME'],
            'table_data' => [
                /* ['John Doe', 'john@example.com', '1234567890', 'Admin'],
                 ['Jane Smith', 'jane@example.com', '9876543210', 'Manager'],
                 ['Bob Ray', 'bob@example.com', '111222333', 'User'],*/
            ],
        ];

        $query = "SELECT
    c.name AS candidate_name,
    c.passport_no AS passport_no,
    f.flight_date,
    TIME_FORMAT(f.flight_time, '%h:%i %p') AS flight_time
FROM flight_schedules f
         JOIN candidates c ON f.candidate_id = c.id
 
    WHERE f.flight_date = CURDATE()
              AND f.flight_time > CURTIME()
   OR f.flight_date > CURDATE()
ORDER BY f.flight_date, f.flight_time ASC;
";
        $table['table_data'] = (array) \DB::select($query);

        return view('voyager::widgets.table-dim', [
            'table_title' => $table['table_title'],
            'table_th'    => $table['table_th'],
            'table_data'  => $table['table_data'],
            'chart_type'  => 'line',
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
