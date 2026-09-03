<?php

namespace App\Http\Controllers\Admin;

use App\Models\Agencier;
use App\Models\Agent;
use App\Models\Candidate;
use App\Models\Companier;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\TicketCompany;
use App\Models\TicketInvoice;
use App\Models\TicketInvoiceLine;
use Illuminate\Http\Request;
use PHPUnit\Exception;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as VoyagerBaseController;

class VoyagerTicketInvoiceController extends VoyagerBaseController
{
    // POST BRE(A)D
    public function store(Request $request)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        //Validate fields
        $val = $this->validateBread($request->all(), $dataType->addRows)->validate();


        //Ticket company set
        $tickerCompany  = TicketCompany::where('id', 1)->first();

        $request->merge([
            'ticket_company_id'  =>  $tickerCompany->id,

            'bank_account_name_1'  =>  $tickerCompany->bank_account_name_1,
            'bank_account_no_1'  =>   $tickerCompany->bank_account_no_1,
            'bank_name_1'  =>  $tickerCompany->bank_name_1 ,
            'bank_branch_name_1'  =>   $tickerCompany->bank_branch_name_1,
            'bank_swift_no_1'  =>   $tickerCompany->bank_swift_no_1,
            'bank_iban_no_1'  =>   $tickerCompany->bank_iban_no_1,

            'bank_account_name_2'  =>   $tickerCompany->bank_account_name_2,
            'bank_account_no_2'  =>   $tickerCompany->bank_account_no_2,
            'bank_name_2'  =>   $tickerCompany->bank_name_2,
            'bank_branch_name_2'  =>  $tickerCompany->bank_branch_name_2 ,
            'bank_swift_no_2'  =>  $tickerCompany->bank_swift_no_2 ,
            'bank_iban_no_2'  => $tickerCompany->bank_iban_no_2
        ]);

        $billTo = $request->get('bill_to');
        if($billTo == 'AGENCIER'){
            $billing  = Agencier::where('id', $request->get('agencier_id'))->first();
            $request->merge([
                'bill_to_name'  =>  $billing->name,
                'bill_to_code'  =>  $billing->code,
                'bill_to_email'  =>  $billing->email,
                'bill_to_mobile'  =>  $billing->mobile,
                'bill_to_address'  =>  $billing->address,
            ]);

        }elseif($billTo == 'COMPANIER'){
            $billing  = Companier::where('id', $request->get('companier_id'))->first();
            $request->merge([
                'bill_to_name'  =>  $billing->name,
                'bill_to_code'  =>  $billing->code,
                'bill_to_email'  =>  $billing->email,
                'bill_to_mobile'  =>  $billing->mobile,
                'bill_to_address'  =>  $billing->address,
            ]);

        }elseif($billTo == 'AGENT'){
            $billing  = Agent::where('id', $request->get('agent_id'))->first();
            $request->merge([
                'bill_to_name'  =>  $billing->name,
                'bill_to_code'  =>  $billing->code,
                'bill_to_email'  =>  $billing->email,
                'bill_to_mobile'  =>  $billing->mobile,
                'bill_to_address'  =>  $billing->present_address_house,
            ]);
        }

        $data = new $dataType->model_name();
        $this->insertUpdateData($request, $slug, $dataType->addRows, $data);

        //line head data
        $ticket_invoice_line_id = $request->get('ticket_invoice_line_id');
        $ticket_invoice_line_is_candidate = $request->get('ticket_invoice_line_is_candidate');
        $ticket_invoice_line_candidate_id = $request->input('ticket_invoice_line_candidate_id', []);
        $ticket_invoice_line_name = $request->get('ticket_invoice_line_name');
        $ticket_invoice_line_passport_no = $request->get('ticket_invoice_line_passport_no');
        $ticket_invoice_line_quantity = $request->get('ticket_invoice_line_quantity');
        $ticket_invoice_line_unit_price = $request->get('ticket_invoice_line_unit_price');
        //$ticket_invoice_line_total_amount = $request->get('ticket_invoice_line_total_amount');

        $invoiceHeadData = [];
        if(count($ticket_invoice_line_id)){
            TicketInvoiceLine::where('ticket_invoice_id', $data->id)->delete();
            foreach ( $ticket_invoice_line_id as $i=>$head){

                if(isset($ticket_invoice_line_is_candidate[$i]) &&  $ticket_invoice_line_is_candidate[$i] == 'CANDIDATE'
                && isset($ticket_invoice_line_candidate_id[$i]) && !empty($ticket_invoice_line_candidate_id[$i]) ){
                    $candidate = Candidate::where('id', $ticket_invoice_line_candidate_id[$i])->first();
                    if($candidate){
                        $candidate_id = $candidate->id;
                        $name = $candidate->name;
                        $passport_no = $candidate->passport_no;
                    }else{
                        $candidate_id = null;
                        $name = $ticket_invoice_line_name[$i];
                        $passport_no = $ticket_invoice_line_passport_no[$i];
                    }
                }else{
                    $candidate_id = null;
                    $name = $ticket_invoice_line_name[$i];
                    $passport_no = $ticket_invoice_line_passport_no[$i];
                }

                $invoiceHeadData[] = [
                    'ticket_invoice_id' =>$data->id,
                    'is_candidate' =>isset($ticket_invoice_line_is_candidate[$i])?$ticket_invoice_line_is_candidate[$i]:null,
                    'candidate_id' => $candidate_id,
                    'name' =>$name,
                    'passport_no' =>$passport_no,
                    'remarks' =>'',
                    'quantity' =>isset($ticket_invoice_line_quantity[$i])?$ticket_invoice_line_quantity[$i]:0,
                    'unit_price' =>isset($ticket_invoice_line_unit_price[$i])?$ticket_invoice_line_unit_price[$i]:0,
                    'total_amount' =>(isset($ticket_invoice_line_unit_price[$i]) && isset($ticket_invoice_line_quantity[$i]))?(float) $ticket_invoice_line_unit_price[$i] * (float) $ticket_invoice_line_quantity[$i]: 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
            if(count($invoiceHeadData)){
                TicketInvoiceLine::insert($invoiceHeadData);
            }
        }
        //when call in update
        if(!empty($request->get('old_id'))){
            return true;
        }

        return redirect()
            ->route("voyager.{$dataType->slug}.index")
            ->with([
                'message'    => __('voyager::generic.successfully added new')." {$dataType->getTranslatedAttribute('display_name_singular')}",
                'alert-type' => 'success',
            ]);
    }

    // POST BR(E)AD
    public function update(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('edit', app($dataType->model_name));

        //Validate fields
        $val = $this->validateBread($request->all(), $dataType->editRows, $dataType->name, $id)->validate();
        $request->merge([
            'old_id'  =>  $id
        ]);

        if($this->store($request)){
            TicketInvoice::where('id', $id)->delete();
            TicketInvoiceLine::where('ticket_invoice_id', $id)->delete();
        }
        return redirect()
            ->route("voyager.{$dataType->slug}.index")
            ->with([
                'message'    => __('voyager::generic.successfully added new')." {$dataType->getTranslatedAttribute('display_name_singular')}",
                'alert-type' => 'success',
            ]);
    }


    public function printInvoice(Request $request, $id){
        ini_set("pcre.backtrack_limit", "5000000"); //todo add it in __construct
        $title = 'Invoice';
        $reportType = 'PDF';
        $fileName = str_replace(' ', '_', $title).'_'.date('Y_m_d_h_i_a');
        $data = TicketInvoice::where('id', $id)->first();
        $title = $title.'-'.$data->invoice_no;

        $downloadable = false;
        $printBlade = 'voyager::reports.print.ticket-invoice';
        $report_type = $request->get('report_type');
        try{
            if($report_type=='WEB'){
                return view($printBlade, [
                    'title'=> $title,
                    'data'=>$data,
                    'report_type'=>$report_type
                ]);
            }
            if($reportType == 'PDF'){
                $pdf = \PDF::loadView($printBlade,
                    [
                        'title'=> $title,
                        'data'=>$data,
                        'report_type'=>$report_type
                    ], [],
                    [
//                        'default_font' => 'freeserif',
//                        'mode' => 'utf-8',
                        'format' => 'A4' //A4, A4-L, A5-L

                    ]
                );

                /*
                                $mpdf = $pdf->getMpdf();
                                $mpdf->SetWatermarkText('CONFIDENTIAL');
                                $mpdf->watermarkTextAlpha = 0.12; // transparency
                                $mpdf->showWatermarkText = true;*/


                if($downloadable){
                    return $pdf->download($fileName.'.pdf');
                }else{
                    return $pdf->stream($fileName.'.pdf');
                }
            }
        }catch (Exception $e){
            echo $e->getMessage();
        }
        return false;

    }

    public function relation(Request $request)
    {
        if (in_array($request->type, ['ticket_invoice_belongsto_candidate_relationship'])) {

            $query =  Candidate::where('status', 'A')
                ->whereHas('classGroup', function ($query) {
                    $query->where('name', 'like', '%Rapid%');
                });


            if ($request->has('search')) {
                $query->where('name', 'LIKE', '%' . $request->search . '%');
//                $query->orWhere('mobile', 'LIKE', '%' . $request->search . '%');
                $query->orWhere('passport_no', 'LIKE', '%' . $request->search . '%');
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
                    'text'=>$d->name.'   ('.$d->passport_no.')'
                ];
            }
            return response()->json($roles);
        }

        return parent::relation($request);
    }



}
