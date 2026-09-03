<?php

namespace App\Http\Controllers\Admin;

use App\Models\Invoice;
use App\Models\InvoiceMoneyReceipt;
use Illuminate\Http\Request;
use PHPUnit\Exception;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as VoyagerBaseController;

class VoyagerInvoiceMoneyReceiptController extends VoyagerBaseController
{
    public function printInvoiceMoneyReceipt(Request $request, $id){
        ini_set("pcre.backtrack_limit", "5000000"); //todo add it in __construct
        $title = 'Invoice';
        $reportType = 'PDF';
        $fileName = str_replace(' ', '_', $title).'_'.date('Y_m_d_h_i_a');
        $data = InvoiceMoneyReceipt::where('invoice_id', $id)->first();
        $title = $title.'-'.$data->invoice->invoice_no;

        $downloadable = false;
        $printBlade = 'voyager::reports.print.invoice-money-receipt';
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
                                          'format' => 'A4-L' //A4, A4-L, A5-L
                                      ]
                );
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

    public function create(Request $request)
    {
        $invoiceId = $request->get('invoice_id');
        if(empty($invoiceId)){
            return redirect()->route("voyager.invoices.index");
        }

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


        //invoice info
        $invoice = Invoice::where('id', $invoiceId)->first();
        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable', 'invoice'));
    }

    public function store(Request $request)
    {
//        dd($request->all());
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows)->validate();
        $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());

        event(new BreadDataAdded($dataType, $data));

        if (!$request->has('_tagging')) {
            if (auth()->user()->can('browse', $data)) {
                //$redirect = redirect()->route("voyager.{$dataType->slug}.index");
                $redirect = redirect()->route("voyager.invoices.index");
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
