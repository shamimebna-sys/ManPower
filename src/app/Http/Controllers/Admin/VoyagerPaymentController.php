<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonClass;
use App\Models\Agent;
use App\Models\Candidate;
use App\Models\Payment;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as VoyagerBaseController;

class VoyagerPaymentController extends VoyagerBaseController
{

    public function store(Request $request)
    {
        $slug = $this->getSlug($request);
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
        $this->authorize('add', app($dataType->model_name));

        $validator = Validator::make($request->all(), [
                'invoice_no' => 'required',
                'title' => 'required',
                'type' => 'required',
                'payment_date' => 'required',
                'amount' => 'required'
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->messages()->getMessages() as $messages) {
                return redirect()->back()->with($this->alertError(__($messages[0])))->withInput();
            }
        }

        $agent_id = strtoupper($request->get('agent_id'));
        $teacher_id = $request->get('teacher_id');
        $candidate_id = $request->get('candidate_id');
        $invoice_no = $request->get('invoice_no');
        $title = $request->get('title');
        $type = $request->get('type');
        $payment_date = $request->get('payment_date');
        $amount = $request->get('amount');
        $bank_name = $request->get('bank_name');
        $acc_no = $request->get('acc_no');
        $trx_no = $request->get('trx_no');
        $status = $request->get('status');

        $account_name = $request->get('account_name');
        $currency_id = $request->get('currency_id');
        $exchange_rate = $request->get('exchange_rate');
        $remarks = $request->get('remarks');
        $description = $request->get('description');

        $cheque_file_path = null;
        if ($request->hasFile('cheque_file_path')) {
            $row = $dataType->addRows->where('field', 'cheque_file_path')->first();
            if ($row) {
                $cheque_file_path = (new \TCG\Voyager\Http\Controllers\ContentTypes\File($request, $slug, $row, $row->details))->handle();
            } else {
                $files = \Illuminate\Support\Arr::wrap($request->file('cheque_file_path'));
                $filesPath = [];
                $path = 'payments/' . date('FY') . '/';

                foreach ($files as $file) {
                    $filename = \Illuminate\Support\Str::random(20);
                    while (\Illuminate\Support\Facades\Storage::disk(config('voyager.storage.disk', 'public'))->exists($path . $filename . '.' . $file->getClientOriginalExtension())) {
                        $filename = \Illuminate\Support\Str::random(20);
                    }

                    $file->storeAs(
                        $path,
                        $filename . '.' . $file->getClientOriginalExtension(),
                        config('voyager.storage.disk', 'public')
                    );

                    array_push($filesPath, [
                        'download_link' => $path . $filename . '.' . $file->getClientOriginalExtension(),
                        'original_name' => $file->getClientOriginalName(),
                    ]);
                }
                $cheque_file_path = json_encode($filesPath);
            }
            if ($cheque_file_path) {
                $cheque_file_path = str_replace('\\', '/', $cheque_file_path);
            }
        }

        DB::beginTransaction();
        $error = 0;
        $errorMsg = [];

        try {
            $transactionTitle = '';
            if(!empty($agent_id)){
                $entity = Agent::where('id', $agent_id)->first();
            }elseif(!empty($teacher_id)){
                $entity = Teacher::where('id', $agent_id)->first();
            }elseif(!empty($candidate_id)){
                $entity = Candidate::where('id', $agent_id)->first();
            }else{
                $entity = false;
            }

            if($type == 'CR' && $entity){
                if (!$entity->increment('balance', $amount)) {
                    $error++;
                    $errorMsg[] = 'Failed to update balance';
                }
                $transactionTitle = 'Balance credited successfully';
            }elseif($type == 'DR' && $entity){
                if ( !$entity->decrement('balance', $amount)) {
                    $error++;
                    $errorMsg[] = 'Failed to update balance';
                }
                $transactionTitle = 'Balance debited successfully';
            }else{
                $error++;
            }


            if($error == 0){
                //Payment
                $entityPayment = new Payment();
                $entityPayment->title = $title. '. '.$transactionTitle;
                $entityPayment->type = $type;
                $entityPayment->agent_id = !empty($agent_id)?$agent_id:0;
                $entityPayment->candidate_id = !empty($candidate_id)?$candidate_id:0;
                $entityPayment->teacher_id = !empty($teacher_id)?$teacher_id:0;
                $entityPayment->amount = $amount;
                $entityPayment->payment_date = $payment_date;
                $entityPayment->invoice_no = $invoice_no;
                $entityPayment->bank_name = $bank_name;
                $entityPayment->acc_no = $acc_no;
                $entityPayment->trx_no = $trx_no;
                $entityPayment->user_id = CommonClass::user()->id;
                $entityPayment->status = $status;

                $entityPayment->account_name = $account_name;
                $entityPayment->currency_id = $currency_id;
                $entityPayment->exchange_rate = $exchange_rate;
                $entityPayment->remarks = $remarks;
                $entityPayment->description = $description;
                $entityPayment->cheque_file_path = $cheque_file_path;

                if (!$entityPayment->save()) {
                    DB::rollBack();
                    $error++;
                    $errorMsg[] = 'Failed to add payment information';
                }
            }


        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $errorMsg[] = 'Something went wrong. Please contact with site developer.'.$e->getMessage();
            $error++;
            DB::rollback();
        }

        if ($error == 0) {
            DB::commit();
//            return redirect()->route("voyager.{$dataType->slug}.create")->with($this->alertSuccess(__('Payment updated successfully.')));
            return redirect()->back()->with($this->alertSuccess(__('Payment updated successfully.')));

        } else {
            return redirect()->back()->with($this->alertError(__($errorMsg[0])))->withInput();
        }
    }

}
