<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonClass;
use App\Models\Candidate;
use App\Models\SubAgent;
use App\Models\SubAgentPaymentRequest as PaymentRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as VoyagerBaseController;

class VoyagerSubAgentPaymentRequestController extends VoyagerBaseController
{
    public function create(Request $request)
    {
        $slug = $this->getSlug($request);


        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? new $dataType->model_name()
            : false;

        foreach ($dataType->addRows as $key => $row) {
            $dataType->addRows[$key]['col_width'] = $row->details->width ?? 100;
        }

        // If a column has a relationship associated with it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'add');

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        // Eagerload Relations
        $this->eagerLoadRelations($dataTypeContent, $dataType, 'add', $isModelTranslatable);

        $view = 'voyager::bread.edit-add';

        if (view()->exists("voyager::$slug.edit-add")) {
            $view = "voyager::$slug.edit-add";
        }



        $candidate_id = $request->get('candidate_id');
        if(!empty($candidate_id)){
            $paymentRequestData = PaymentRequest::where('candidate_id',$candidate_id )->orderBy('id', 'desc')->get();
        }else{
            $paymentRequestData =false;
        }
        /// /////////////////datatable end////////////

        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'paymentRequestData', 'candidate_id'
        ));
    }

    public function store(Request $request)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows)->validate();

        $candidate = Candidate::find( $request->get('candidate_id'));
        $request->merge(['sub_agent_id' => $candidate->sub_agent_id]);
        $request->merge(['group_id' => $candidate->class_group_id]);
        $request->merge(['bill_code' => 101]);

        $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());

        event(new BreadDataAdded($dataType, $data));

        if ($data) {
            $candidate = \App\Models\Candidate::find($data->candidate_id);
            if ($candidate) {
                $amountFormatted = CommonClass::currencySymbol(true, $data->currency_id, $data->amount);
                $title = 'New Candidate Payment Request';
                $body = 'A new payment request of ' . $amountFormatted . ' (' . $data->bill_title . ') has been requested for candidate ' . $candidate->name . ' (Code: ' . $candidate->code . ').';

                if (!empty($candidate->agent_id)) {
                    CommonClass::sendTargetedNotificationAndEmail('agent', $candidate->agent_id, $title, $body);
                }
                if (!empty($candidate->sub_agent_id)) {
                    CommonClass::sendTargetedNotificationAndEmail('sub_agent', $candidate->sub_agent_id, $title, $body);
                }
            }
        }

        if (!$request->has('_tagging')) {
            /* if (auth()->user()->can('browse', $data)) {
                 $redirect = redirect()->route("voyager.{$dataType->slug}.index");
             } else {
                 $redirect = redirect()->back();
             }*/

            $redirect = redirect()->back();
            return $redirect->with([
                'message'    => __('voyager::generic.successfully_added_new')." {$dataType->getTranslatedAttribute('display_name_singular')}",
                'alert-type' => 'success',
            ]);
        } else {
            return response()->json(['success' => true, 'data' => $data]);
        }
    }


    public function edit(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);
            $query = $model->query();

            // Use withTrashed() if model uses SoftDeletes and if toggle is selected
            if ($model && in_array(SoftDeletes::class, class_uses_recursive($model))) {
                $query = $query->withTrashed();
            }
            if ($dataType->scope && $dataType->scope != '' && method_exists($model, 'scope'.ucfirst($dataType->scope))) {
                $query = $query->{$dataType->scope}();
            }
            $dataTypeContent = call_user_func([$query, 'findOrFail'], $id);
        } else {
            // If Model doest exist, get data from table name
            $dataTypeContent = DB::table($dataType->name)->where('id', $id)->first();
        }

        foreach ($dataType->editRows as $key => $row) {
            $dataType->editRows[$key]['col_width'] = isset($row->details->width) ? $row->details->width : 100;
        }

        // If a column has a relationship associated with it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'edit');

        // Check permission
        $this->authorize('edit', $dataTypeContent);

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        // Eagerload Relations
        $this->eagerLoadRelations($dataTypeContent, $dataType, 'edit', $isModelTranslatable);

        $view = 'voyager::bread.edit-add';

        if (view()->exists("voyager::$slug.edit-add")) {
            $view = "voyager::$slug.edit-add";
        }

        ////////////////////////datatable start////////////////////////
        $candidate_id = $request->get('candidate_id');
        if(!empty($candidate_id)){
            $paymentRequestData = PaymentRequest::where('candidate_id',$candidate_id )->orderBy('id', 'desc')->get();

        }else{
            $paymentRequestData = false;
        }
        /// /////////////////datatable end////////////

        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'paymentRequestData', 'candidate_id'));
    }


    public function relation(Request $request)
    {
        if (in_array($request->type, ['sub_agent_payment_request_belongsto_candidate_relationship'])) {

            $id = $request->get('id');
            $agentId = $request->get('sub_agent_id');
            $groupId = $request->get('group_id');

            if(!empty($agentId) && !empty($groupId)){
                $query = Candidate::where('sub_agent_id', $agentId)->where('class_group_id', $groupId);
            }elseif (!empty($id)){
                $paymentRequest = PaymentRequest::find($id);
                $agentId = $paymentRequest->sub_agent_id;
                $groupId = $paymentRequest->group_id;
                $query = Candidate::where('sub_agent_id', $agentId)->where('class_group_id', $groupId);
            }else{
                $query = Candidate::where('id',  0);
            }

            if ($request->has('search')) {
                $query->where('name', 'LIKE', '%' . $request->search . '%');
                $query->orWhere('code', 'LIKE', '%' . $request->search . '%');
                $query->orWhere('mobile', 'LIKE', '%' . $request->search . '%');
            }

            $roles = [
                'pagination'=>['more'=>false],
                'results'=>[
//                    ['id'=>'','text'=>'None' ]
                ]
            ];
            foreach ($query->get() as $d){
                $roles['results'][] = [
                    'id'=>$d->id,
                    'text'=>' ('.$d->code.') '.$d->name.' - '.$d->mobile
                ];
            }
            return response()->json($roles);
        }

        //load sub agent by agent and search sub agent
        if (in_array($request->type, ['sub_agent_payment_request_belongsto_sub_agent_relationship'])) {
            if(CommonClass::user()->role_id == 101){
                $query = SubAgent::where('agent_id', CommonClass::user()->profile_id);
            }else{
                $query = SubAgent::where('id', '>', 0);
            }

            if ($request->has('search')) {
                $query->where('name', 'LIKE', '%' . $request->search . '%');
                $query->orWhere('code', 'LIKE', '%' . $request->search . '%');
                $query->orWhere('mobile', 'LIKE', '%' . $request->search . '%');
            }

            $roles = [
                'pagination'=>['more'=>false],
                'results'=>[
//                    ['id'=>'','text'=>'None' ]
                ]
            ];
            foreach ($query->get() as $d){
                $roles['results'][] = [
                    'id'=>$d->id,
                    'text'=>$d->name.' ('.$d->code.')'
                ];
            }
            return response()->json($roles);
        }

        return parent::relation($request);
    }
}
