<?php

namespace App\Http\Controllers\Admin;

use App\Models\Candidate;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Agencier;
use Illuminate\Http\Request;
use PHPUnit\Exception;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as VoyagerBaseController;

class VoyagerInvoiceController extends VoyagerBaseController
{
    // POST BRE(A)D
    public function store(Request $request)
    {
		

        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
		
		$agenciers_id = $request->get('agenciers_id');
		if(!empty($agenciers_id)){
			$agenciers = Agencier::where('id', $agenciers_id)->first();
			//dd($agenciers);
			$request->merge([
				'bank_account_name' => $agenciers->bank_account_name, 
				'bank_account_no' => $agenciers->bank_account_no, 
				'bank_name' => $agenciers->bank_name, 
				'bank_branch_name' => $agenciers->bank_branch_name, 
				'bank_iban_no' => $agenciers->bank_iban_no, 
				'bank_swift_no' => $agenciers->bank_swift_no
			]);
			
		}
		
//dd($request->all());

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        //Validate fields
        $val = $this->validateBread($request->all(), $dataType->addRows)->validate();

        $data = new $dataType->model_name();
        $this->insertUpdateData($request, $slug, $dataType->addRows, $data);

        //line head data
        $line_head_id = $request->get('line_head_id');
        $line_description = $request->get('line_description');
        $line_id = $request->get('line_id');
        $line_quantity = $request->get('line_quantity');
        $line_unit_price = $request->get('line_unit_price');
        $line_sub_total_amount = $request->get('line_sub_total_amount');
        $line_vat_rate = $request->get('line_vat_rate');
        $line_vat_amount = $request->get('line_vat_amount');
        $line_total_amount = $request->get('line_total_amount');

        $invoiceHeadData = [];
        if(count($line_head_id)){
            InvoiceLine::where('invoice_id', $data->id)->delete();
            foreach ( $line_head_id as $i=>$head){
                $invoiceHeadData[] = [
                    'invoice_id' =>$data->id,
                    'invoice_head_id' =>$line_head_id[$i],
                    'description' =>$line_description[$i],
                    'quantity' =>$line_quantity[$i],
                    'unit_price' =>$line_unit_price[$i],
                    'sub_total_amount' =>$line_sub_total_amount[$i],
                    'vat_rate' =>$line_vat_rate[$i],
                    'vat_amount' =>$line_vat_amount[$i],
                    'total_amount' =>$line_total_amount[$i],
                ];
            }
            if(count($invoiceHeadData)){
                InvoiceLine::insert($invoiceHeadData);
            }
        }

//        $data->permissions()->sync($request->input('permissions', []));

        return redirect()
            ->route("voyager.{$dataType->slug}.index")
            ->with([
                       'message'    => __('voyager::generic.successfully_added_new')." {$dataType->getTranslatedAttribute('display_name_singular')}",
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

        $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);
        $this->insertUpdateData($request, $slug, $dataType->editRows, $data);

        //line head data
        $line_head_id = $request->get('line_head_id');
        $line_description = $request->get('line_description');
        $line_id = $request->get('line_id');
        $line_quantity = $request->get('line_quantity');
        $line_unit_price = $request->get('line_unit_price');
        $line_sub_total_amount = $request->get('line_sub_total_amount');
        $line_vat_rate = $request->get('line_vat_rate');
        $line_vat_amount = $request->get('line_vat_amount');
        $line_total_amount = $request->get('line_total_amount');

        $invoiceHeadData = [];
        if(count($line_head_id)){
            InvoiceLine::where('invoice_id', $data->id)->delete();
            foreach ( $line_head_id as $i=>$head){
                $invoiceHeadData[] = [
                    'invoice_id' =>$data->id,
                    'invoice_head_id' =>$line_head_id[$i],
                    'description' =>$line_description[$i],
                    'quantity' =>$line_quantity[$i],
                    'unit_price' =>$line_unit_price[$i],
                    'sub_total_amount' =>$line_sub_total_amount[$i],
                    'vat_rate' =>$line_vat_rate[$i],
                    'vat_amount' =>$line_vat_amount[$i],
                    'total_amount' =>$line_total_amount[$i],
                ];
            }
            if(count($invoiceHeadData)){
                InvoiceLine::insert($invoiceHeadData);
            }
        }

//        $data->permissions()->sync($request->input('permissions', []));

        return redirect()
            ->route("voyager.{$dataType->slug}.index")
            ->with([
                       'message'    => __('voyager::generic.successfully_updated')." {$dataType->getTranslatedAttribute('display_name_singular')}",
                       'alert-type' => 'success',
                   ]);
    }


    public function printInvoice(Request $request, $id){
        ini_set("pcre.backtrack_limit", "5000000"); //todo add it in __construct
        $title = 'Invoice';
        $reportType = 'PDF';
        $fileName = str_replace(' ', '_', $title).'_'.date('Y_m_d_h_i_a');
        $data = Invoice::where('id', $id)->first();
        $title = $title.'-'.$data->invoice_no;

        $downloadable = false;
        $printBlade = 'voyager::reports.print.invoice';
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
        if (in_array($request->type, ['invoice_belongstomany_candidate_relationship'])) {

            $id = $request->get('id');
            $company_id = $request->get('company_id');

            if(!empty($company_id)){
                $query = Candidate::where('companier_id', $company_id);
            }else{
                $query = Candidate::where('id',  0);
            }

            if ($request->has('search')) {
                $query->where('name', 'LIKE', '%' . $request->search . '%');
//                $query->orWhere('code', 'LIKE', '%' . $request->search . '%');
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
                    'text'=>$d->name.' ('.$d->passport_no.')'
                ];
            }
            return response()->json($roles);
        }

        return parent::relation($request);
    }



}
