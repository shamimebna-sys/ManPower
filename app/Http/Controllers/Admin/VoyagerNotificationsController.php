<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonClass;
use App\Models\Agent;
use App\Models\Candidate;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Events\BreadDataUpdated;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as VoyagerBaseController;
use TCG\Voyager\Models\User;

class VoyagerNotificationsController extends VoyagerBaseController
{

    public function store(Request $request)
    {
        try{

            $slug = $this->getSlug($request);

            $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

            // Check permission
//        $this->authorize('add', app($dataType->model_name));

            $val = $this->validateBread($request->all(), $dataType->addRows)->validate();

            //$classGroupId = $request->get('group_id');
            $classGroupId = $request->get('notification_belongsto_class_group_relationship');
//            dd($classGroupId);
            $redirectUrl = $request->get('redirect_url');
            $messageCode = date('ymdhsi').rand(111,9999);
            $requestData = $request->all();
//            $candidates = Candidate::with('user')->where('class_group_id', $classGroupId)->get();
//            dd(Candidate::with('user')->whereIn('class_group_id', $classGroupId)->get());
            $candidates = Candidate::with('user')->whereIn('class_group_id', $classGroupId)->get();
            if($candidates){
                foreach ( $candidates as $candidate) {
                    if($candidate->user && isset($candidate->user->id)){
                        $request->merge([
                            'user_id' => $candidate->user->id,
                            'code' => $messageCode,
                            'redirect_url' => $redirectUrl,
                            'group_id' => $candidate->class_group_id
                        ]);
                        $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());
                        event(new BreadDataAdded($dataType, $data));
                    }
                }
            }else{
                $redirect = redirect()->back();
                return $redirect->with([
                    'message'    =>"Sorry No group candidate founds",
                    'alert-type' => 'danger',
                ]);
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
        }catch (\Exception $e){
//            dd($e->getMessage());
            $redirect = redirect()->back();
            return $redirect->with([
                'message'    =>"Sorry No group candidate founds",
                'alert-type' => 'error',
            ]);
        }
    }

}
