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

        // Get original class group IDs
        $originalClassGroupIds = $request->input('class_group_id');
        
        // Convert array to comma-separated string for validation and persistence
        $commaSeparatedIds = '';
        if (is_array($originalClassGroupIds)) {
            $commaSeparatedIds = implode(',', array_filter($originalClassGroupIds));
        } else {
            $commaSeparatedIds = $originalClassGroupIds;
        }

        $request->merge(['class_group_id' => $commaSeparatedIds]);

        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows)->validate();

        $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());

        if ($data) {
            event(new BreadDataAdded($dataType, $data));

            // Send notification
            $classGroupIds = array_filter(explode(',', $commaSeparatedIds));
            $redirectUrl = $data->exam_link;
            $title = $data->name . ' Exam Invitation ';
            $message = '<p><b>Exam Datetime: </b>' . date('Y-m-d', strtotime($data->exam_date)) . '</p>';
            $message .= '<p><b>Exam Time: </b>' . date('h:i A', strtotime($data->exam_time)) . '</p>';
            if (!empty($data->remarks)) {
                $message .= '<p><b>Note: </b>' . $data->remarks . '</p>';
            }

            foreach ($classGroupIds as $classGroupId) {
                if (empty($classGroupId)) {
                    continue;
                }
                $messageCode = date('ymdhsi') . $classGroupId . '_' . rand(100, 999);
                $candidates = Candidate::with('user')->where('class_group_id', $classGroupId)->get();
                if ($candidates) {
                    foreach ($candidates as $candidate) {
                        if ($candidate->user && isset($candidate->user->id)) {
                            CommonClass::sendNotificationAndEmail($messageCode, $classGroupId, $candidate->user->id, $title, $message, $redirectUrl, false);
                        }
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
                'message'    => __('voyager::generic.successfully_added_new') . " {$dataType->getTranslatedAttribute('display_name_singular')}",
                'alert-type' => 'success',
            ]);
        } else {
            return response()->json(['success' => true, 'data' => $data]);
        }
    }

    public function update(Request $request, $id)
    {
        if ($request->has('class_group_id') && is_array($request->input('class_group_id'))) {
            $classGroupIds = $request->input('class_group_id');
            $commaSeparatedIds = implode(',', array_filter($classGroupIds));
            $request->merge(['class_group_id' => $commaSeparatedIds]);
        }

        return parent::update($request, $id);
    }
}
