<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonClass;
use App\Models\ClassSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as VoyagerBaseController;

class VoyagerClassScheduleController extends VoyagerBaseController
{

    public function store(Request $request)
    {
        $slug = $this->getSlug($request);
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
        $this->authorize('add', app($dataType->model_name));

        $validator = Validator::make($request->all(), [
                'start_time' => 'required',
                'end_time' => 'required',
                'week_day' => 'required',
                'status' => 'required'
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->messages()->getMessages() as $messages) {
                return redirect()->back()->with($this->alertError(__($messages[0])))->withInput();
            }
        }

        $teacher_id = strtoupper($request->get('teacher_id'));
        $class_group_id = $request->get('class_group_id');
        $subject = $request->get('subject');
        $start_time = $request->get('start_time');
        $end_time = $request->get('end_time');
        $week_day = $request->get('week_day');
        $status = $request->get('status');


        DB::beginTransaction();
        $error = 0;
        $errorMsg = [];

        try {
            $entity = new ClassSchedule();
            $entity->teacher_id = !empty($teacher_id)?$teacher_id:0;
            $entity->class_group_id = !empty($class_group_id)?$class_group_id:0;
            $entity->subject = $subject;
            $entity->start_time = $start_time;
            $entity->end_time = $end_time;
            $entity->week_day = $week_day;
            $entity->status = $status;

            if (!$entity->save()) {
                DB::rollBack();
                $error++;
                $errorMsg[] = 'Failed to add schedule information';
            }


        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $errorMsg[] = 'Something went wrong. Please contact with site developer.'.$e->getMessage();
            $error++;
            DB::rollback();
        }

        if ($error == 0) {
            DB::commit();
            return redirect()->back()->with($this->alertSuccess(__('Schedule updated successfully.')));

        } else {
            return redirect()->back()->with($this->alertError(__($errorMsg[0])))->withInput();
        }
    }
}
