<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonClass;
use App\Models\Agent;
use App\Models\Candidate;
use App\Models\EmployerCandidate;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PaymentRequest;
use App\Models\SubAgentPaymentRequest;
use App\Models\SubAgent;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use TCG\Voyager\Database\Schema\SchemaManager;
use TCG\Voyager\Events\BreadAdded;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as VoyagerBaseController;

class VoyagerAjaxController extends VoyagerBaseController
{
    public function classGroupCandidates(Request $request, $id)
    {
        $classGroup = \App\Models\ClassGroup::findOrFail($id);

        $candidates = Candidate::with([
            'agent',
            'agencier',
            'companier',
            'positionRelation',
            'VisaImmigration'
        ])->where('class_group_id', $id)->get();

        return view('vendor.voyager.ajaxs.class-group-candidates', compact('classGroup', 'candidates'));
    }

    public function notifications(Request $request){

		if(Auth::user()){
			$data = Notification::where('user_id', Auth::user()->id)->orderBy('created_at','desc')->limit(20)->get();
			Notification::where('user_id', Auth::id())->where('is_seen', 'N')->update(['is_seen'=>'Y']);

			return view('vendor.voyager.ajaxs.notifications', ['data'=>$data]);
		}else{
			return redirect('/');
		}

    }
    public function notificationCounter(Request $request){
        if(Auth::user()){
            $count = Notification::where('user_id', Auth::user()->id)->where('is_seen', 'N')->count();
            return response()->json(['status' => true, 'code' => 1, 'msg' => 'Number of new notification', 'count'=>$count], 200);
        }else{
            return response()->json(['status' => false, 'code' => 1, 'msg' => 'User has been logout', 'count'=>0], 200);
        }

    }

    public function paymentApproval(Request $request,$id){
        if($request->isMethod('POST')){
            $validator = Validator::make($request->all(), [
                'exchange_rate' => 'required',
                'remarks' => 'required',
                'status' => 'required',
            ],
            [
                'status.required'=>'Invalid operation'
            ]);
            if ($validator->fails()) {
                foreach ($validator->messages()->getMessages() as $messages) {
                    return response()->json(['status' => false, 'code' => 998, 'msg' => $messages[0]], 200);
                }
            }

            $exchange_rate = (float) $request->get('exchange_rate');

            $payment = Payment::find($id);
            if( $payment->status == 'P'){
                $payment->status =  $request->get('status');
                $payment->remarks =  $request->get('remarks');
                $payment->exchange_rate =  $exchange_rate;

                if($payment->update()){
                    $statusText = ($payment->status == 'A' ? 'Approved' : 'Rejected');
                    $amountFormatted = CommonClass::currencySymbol(true, $payment->currency_id, $payment->amount);
                    $title = 'Wallet Payment ' . $statusText;
                    $body = 'Your payment request of ' . $amountFormatted . ' (Invoice: ' . $payment->invoice_no . ') has been ' . strtolower($statusText) . '. Remarks: ' . $payment->remarks;

                    // Send to Owner
                    CommonClass::sendTargetedNotificationAndEmail('owner', null, $title, $body);

                    // Send to Agent
                    if (!empty($payment->agent_id)) {
                        CommonClass::sendTargetedNotificationAndEmail('agent', $payment->agent_id, $title, $body);
                    }

                    // Send to Sub Agent
                    if (!empty($payment->sub_agent_id)) {
                        CommonClass::sendTargetedNotificationAndEmail('sub_agent', $payment->sub_agent_id, $title, $body);
                    }

                    if($payment->status == 'A'){
                        if(CommonClass::user()->role_id == 101){ //subagent payment approval by agent
                            $agent   = SubAgent::where('id', $payment->sub_agent_id)->first();


                        }else{ //agent payment approval by management
                            $agent   = Agent::where('id', $payment->agent_id)->first();
                        }
                        if(empty($agent->balance)){
                            $agent->balance = $payment->amount/ $exchange_rate;
                            if($agent->update()){
                                return response()->json(['status' => true, 'code' => 1, 'msg' => 'Balance credited  successfully'], 200);
                            }
                        }elseif ($agent->increment('balance', $payment->amount/ $exchange_rate)) {
                            return response()->json(['status' => true, 'code' => 1, 'msg' => 'Balance credited  successfully'], 200);
                        }
                    }else{
                        return response()->json(['status' => true, 'code' => 1, 'msg' => 'Payment processed  successfully'], 200);
                    }

                }
                return response()->json(['status' => false, 'code' => 99, 'msg' => 'Payment updated failed'], 200);

            }else{
                return response()->json(['status' => false, 'code' => 99, 'msg' => 'Payment already processed'], 200);
            }
        }

        $data = Payment::with(['subAgent'])
            ->join('agents', 'payments.agent_id', '=', 'agents.id')
            ->where('payments.id',$id)
             ->select('agents.code as agent_code', 'agents.name as agent_name',  'payments.*')
            ->first();
       // Log::info($data);
        return view('vendor.voyager.ajaxs.payment-approval', ['data'=>$data]);
    }

    public function candidateApproval(Request $request,$id){
        if($request->isMethod('POST')){
            $validator = Validator::make($request->all(), [
                'remarks' => 'required',
                'status' => 'required',
            ],
            [
                'status.required'=>'Invalid operation'
            ]);
            if ($validator->fails()) {
                foreach ($validator->messages()->getMessages() as $messages) {
                    return response()->json(['status' => false, 'code' => 998, 'msg' => $messages[0]], 200);
                }
            }

            $entity = Candidate::find($id);
            if( $entity->status == 'P'){
                $entity->status =  $request->get('status');
                $entity->remarks =  $request->get('remarks');
                if($entity->update()){
                    return response()->json(['status' => true, 'code' => 1, 'msg' => 'Candidate status updated  successfully'], 200);
                }
                return response()->json(['status' => false, 'code' => 99, 'msg' => 'Candidate status update failed'], 200);

            }else{
                return response()->json(['status' => false, 'code' => 99, 'msg' => 'Candidate status already processed'], 200);
            }
        }

        $data = Candidate::with('classGroup')->find($id);
        return view('vendor.voyager.ajaxs.candidate-approval', ['data'=>$data]);
    }

    public function candidatePaymentApproval(Request $request,$id){
//dd('In maintenance. Please wait');
        if($request->isMethod('POST')){
            $validator = Validator::make($request->all(), [
                'remarks' => 'required',
                'status' => 'required',
            ],
            [
                'status.required'=>'Invalid operation'
            ]);
            if ($validator->fails()) {
                foreach ($validator->messages()->getMessages() as $messages) {
                    return response()->json(['status' => false, 'code' => 998, 'msg' => $messages[0]], 200);
                }
            }

            try{
                if(CommonClass::user()->role_id == 109){
                    $paymentRequest = SubAgentPaymentRequest::find($id);
                    $agent = SubAgent::find($paymentRequest->sub_agent_id);
                }else{
                    $paymentRequest = PaymentRequest::find($id);
                    $agent = Agent::find($paymentRequest->agent_id);
                }


                if($agent->balance < $paymentRequest->amount){
                    return response()->json(['status' => false, 'code' => 998, 'msg' => 'Sorry! Your balance is Insufficient. '], 200);
                }else{
                    $amountNew = ($request->get('status') == 'A')?$paymentRequest->amount:0;
                    if($agent->decrement('balance', $amountNew)){

                        $entityPayment = new Payment();
                        $entityPayment->title = $paymentRequest->bill_title.' for candidate '.$paymentRequest->candidate->candidate_details_info;
                        $entityPayment->type = 'DR';
                        $entityPayment->sub_agent_id = isset($paymentRequest->sub_agent_id)?$paymentRequest->sub_agent_id:0;
                        $entityPayment->agent_id = isset($paymentRequest->agent_id)?$paymentRequest->agent_id:0;
                        $entityPayment->candidate_id = $paymentRequest->candidate_id;
                        $entityPayment->teacher_id = 0;
                        $entityPayment->amount = $paymentRequest->amount;
                        $entityPayment->payment_date = now();
                        $entityPayment->invoice_no = CommonClass::invoiceNo();
                        $entityPayment->bank_name = '';
                        $entityPayment->acc_no = '';
                        $entityPayment->trx_no = '';
                        $entityPayment->user_id = CommonClass::user()->id;
                        $entityPayment->status = $request->get('status');
                        $entityPayment->currency_id = 2;
                        $entityPayment->payment_method = 'SELF';
                        $entityPayment->payment_time = now();

                        if ($entityPayment->save()) {
                            if( $paymentRequest->status == 'P'){
                                $paymentRequest->payment_id =  $entityPayment->id;
                                $paymentRequest->status =  $request->get('status');
                                $paymentRequest->remarks =  $request->get('remarks');

                                if($paymentRequest->update()){
                                    // Send Notification & Email to Agent & Sub Agent
                                    try {
                                        $candidate = Candidate::find($paymentRequest->candidate_id);
                                        if ($candidate) {
                                            $statusText = ($paymentRequest->status == 'A' ? 'Approved' : 'Rejected');
                                            $amountFormatted = CommonClass::currencySymbol(true, $paymentRequest->currency_id, $paymentRequest->amount);
                                            $title = 'Candidate Payment ' . $statusText;
                                            $body = 'Payment request of ' . $amountFormatted . ' (' . $paymentRequest->bill_title . ') for candidate ' . $candidate->name . ' has been ' . strtolower($statusText) . '. Remarks: ' . $paymentRequest->remarks;

                                            if (!empty($candidate->agent_id)) {
                                                CommonClass::sendTargetedNotificationAndEmail('agent', $candidate->agent_id, $title, $body);
                                            }
                                            if (!empty($candidate->sub_agent_id)) {
                                                CommonClass::sendTargetedNotificationAndEmail('sub_agent', $candidate->sub_agent_id, $title, $body);
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        Log::error('Candidate Payment Approval Notification error: ' . $e->getMessage());
                                    }

                                    if(CommonClass::user()->role_id == 109){// for subagent no need further process
                                        return response()->json(['status' => true, 'code' => 1, 'msg' => 'Payment Request status updated  successfully'], 200);
                                    }


                                    $candidate = Candidate::find($paymentRequest->candidate_id);
                                    if($paymentRequest->bill_title = 'Admission Group Approval'){
                                        $candidate->admission_payment_id =  $entityPayment->id;
                                        $candidate->update();

                                    }elseif ($paymentRequest->bill_title = 'Final Group Approval'){
                                        $candidate->final_group_payment_id =  $entityPayment->id;
                                        $candidate->update();

                                    }elseif ($paymentRequest->bill_title = 'Medical Fee'){
                                        $candidate->medical_fee_payment_id =  $entityPayment->id;
                                        $candidate->update();
                                    } else{
                                        return response()->json(['status' => false, 'code' => 99, 'msg' => 'Payment status update failed'], 200);
                                    }


                                    return response()->json(['status' => true, 'code' => 1, 'msg' => 'Payment Request status updated  successfully'], 200);
                                }
                                return response()->json(['status' => false, 'code' => 99, 'msg' => 'Payment status update failed'], 200);

                            }else{
                                return response()->json(['status' => false, 'code' => 99, 'msg' => 'Payment status already processed'], 200);
                            }
                        }else{
                            return response()->json(['status' => false, 'code' => 901, 'msg' => 'Payment status update failed'], 200);
                        }

                    }else{
                        return response()->json(['status' => false, 'code' => 997, 'msg' => 'Sorry! Something went wrong. Try again later. '], 200);
                    }
                }
            }catch (\Exception $e){
                return response()->json(['status' => false, 'code' => 999, 'msg' => 'Sorry! Something went wrong. Try again later.', 'error'=>$e->getMessage()], 200);
            }


        }

        if(CommonClass::user()->role_id == 109){
            $paymentRequest = SubAgentPaymentRequest::find($id);
        }else{
            $paymentRequest = PaymentRequest::find($id);
        }

        $candidate = Candidate::with('classGroup')->find($paymentRequest->candidate_id);
        return view('vendor.voyager.ajaxs.candidate-payment-approval', ['paymentRequest'=>$paymentRequest, 'candidate'=>$candidate]);
    }


    public function employerCandidateAddRemove(Request $request){
        if($request->isMethod('POST')){
            $validator = Validator::make($request->all(), [
                'employer_id' => 'required',
                'candidate_id' => 'required',
                'purpose' => 'required',
            ]);
            if ($validator->fails()) {
                foreach ($validator->messages()->getMessages() as $messages) {
                    return response()->json(['status' => false, 'code' => 998, 'msg' => $messages[0]], 200);
                }
            }

            $candidate_id = $request->get('candidate_id');
            $employer_id = $request->get('employer_id');
            $purpose = $request->get('purpose');

            $entityExist = EmployerCandidate::where('candidate_id', $candidate_id)
                ->where('purpose', strtoupper($purpose))
                ->where('status', 'A')
                ->where('employer_id', $employer_id)->first();

            if($entityExist){
                $entityExist->status = 'I';
                $entityExist->updated_at = date('Y-m-d H:i:s');
//                $entityExist->update();
                $entityExist->delete();
                return response()->json(['status' => false, 'code' => 99, 'msg' => 'Candidate successfully remove from  '. strtolower($purpose)], 200);

            }else{
                $data = [
                    'employer_id' => $employer_id,
                    'candidate_id' => $candidate_id,
                    'purpose' => strtoupper($purpose),
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                EmployerCandidate::insert($data);
                return response()->json(['status' => true, 'code' => 99, 'msg' => 'Candidate successfully make '. strtolower($purpose)], 200);

            }
        }

        return response()->json(['status' => false, 'code' => 99, 'msg' => 'Candidate already processed'], 200);
    }


}
