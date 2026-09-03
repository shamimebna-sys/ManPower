<?php

namespace App\Widgets;

use Illuminate\Support\Facades\Auth;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Widgets\BaseDimmer;

class TodaysCalss extends BaseDimmer
{

    public function run()
    {
        $table = [
            'table_title' => 'TODAYS CLASS',
            'table_th' => ['TEACHER NAME', 'GROUP NAME','SUBJECT','TIME'],
            'table_data' => [
                /* ['John Doe', 'john@example.com', '1234567890', 'Admin'], */
            ],
        ];

        /*$query = "
        SELECT
    t.name AS teacher_name,
    g.name AS group_name,
    s.subject,
   s.week_day,
    CONCAT(
            TIME_FORMAT(s.start_time, '%h:%i %p'),
            ' - ',
            TIME_FORMAT(s.end_time, '%h:%i %p')
    ) AS schedule_time
FROM class_schedules s
         JOIN teachers t ON s.teacher_id = t.id
         JOIN class_groups g ON s.class_group_id = g.id
WHERE s.week_day = DAYNAME(CURDATE())
ORDER BY g.name, t.name, s.start_time ASC
";*/
        $query = "
        SELECT
    t.name AS teacher_name,
    g.name AS group_name,
    s.subject, 
    CONCAT(
            TIME_FORMAT(s.start_time, '%h:%i %p'),
            ' - ',
            TIME_FORMAT(s.end_time, '%h:%i %p')
    ) AS schedule_time
FROM class_schedules s
         JOIN teachers t ON s.teacher_id = t.id
         JOIN class_groups g ON s.class_group_id = g.id
WHERE s.week_day = DAYNAME(CURDATE())
ORDER BY g.name, t.name, s.start_time ASC
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
