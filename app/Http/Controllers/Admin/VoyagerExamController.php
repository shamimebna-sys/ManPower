<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonClass;
use App\Models\Candidate;
use App\Models\Notification;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as VoyagerBaseController;

class VoyagerExamController extends VoyagerBaseController
{
    public function store(Request $request)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows)->validate();
        $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());

        event(new BreadDataAdded($dataType, $data));

        //send notification
        if($data){
            $classGroupId = $data->class_group_id;
            $redirectUrl = $data->exam_link;
            $messageCode = date('ymdhsi').$classGroupId;
            $candidates = Candidate::with('user')->where('class_group_id', $classGroupId)->get();
            $title = $data->name.' Exam Invitation ';
            $message = '<p><b>Exam Datetime: </b>'.date('Y-m-d', strtotime($data->exam_date)).'</p>';
            $message .= '<p><b>Exam Time: </b>'.date('h:i A',strtotime($data->exam_time)).'</p>';
            if(!empty($data->remarks)){
                $message .= '<p><b>Note: </b>'.$data->remarks.'</p>';
            }

            if($candidates){
                foreach ( $candidates as $candidate) {
                    if($candidate->user && isset($candidate->user->id)){
                        CommonClass::sendNotificationAndEmail($messageCode, $classGroupId, $candidate->user->id, $title, $message, $redirectUrl, false);
                    }
                }
            }
        }



        if (!$request->has('_tagging')) {
            if (auth()->user()->can('browse', $data)) {
                $redirect = redirect()->route("voyager.{$dataType->slug}.index");
            } else {
                $redirect = redirect()->back();
            }

            return $redirect->with([
                'message'    => __('voyager::generic.successfully_added_new')." {$dataType->getTranslatedAttribute('display_name_singular')}",
                'alert-type' => 'success',
            ]);
        } else {
            return response()->json(['success' => true, 'data' => $data]);
        }
    }
}
