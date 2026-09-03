<?php

namespace App\Http\Controllers\Admin;

use App\Models\Agent;
use App\Models\Candidate;
use App\Models\Payment;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Exception;
use Shuchkin\SimpleXLSXGen;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as VoyagerBaseController;
use niklasravnsborg\LaravelPdf\Pdf;

class VoyagerReportController extends VoyagerBaseController
{

    public function index(Request $request)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        $getter = $dataType->server_side ? 'paginate' : 'get';

        $search = (object) ['value' => $request->get('s'), 'key' => $request->get('key'), 'filter' => $request->get('filter')];

        $searchNames = [];
        if ($dataType->server_side) {
            $searchNames = $dataType->browseRows->mapWithKeys(function ($row) {
                return [$row['field'] => $row->getTranslatedAttribute('display_name')];
            });
        }

        $orderBy = $request->get('order_by', $dataType->order_column);
        $sortOrder = $request->get('sort_order', $dataType->order_direction);
        $usesSoftDeletes = false;
        $showSoftDeleted = false;

        // Next Get or Paginate the actual content from the MODEL that corresponds to the slug DataType
        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);

            $query = $model::select($dataType->name.'.*');

            if ($dataType->scope && $dataType->scope != '' && method_exists($model, 'scope'.ucfirst($dataType->scope))) {
                $query->{$dataType->scope}();
            }

            // Use withTrashed() if model uses SoftDeletes and if toggle is selected
            if ($model && in_array(SoftDeletes::class, class_uses_recursive($model)) && Auth::user()->can('delete', app($dataType->model_name))) {
                $usesSoftDeletes = true;

                if ($request->get('showSoftDeleted')) {
                    $showSoftDeleted = true;
                    $query = $query->withTrashed();
                }
            }

            // If a column has a relationship associated with it, we do not want to show that field
            $this->removeRelationshipField($dataType, 'browse');

            if ($search->value != '' && $search->key && $search->filter) {
                $search_filter = ($search->filter == 'equals') ? '=' : 'LIKE';
                $search_value = ($search->filter == 'equals') ? $search->value : '%'.$search->value.'%';

                $searchField = $dataType->name.'.'.$search->key;
                if ($row = $this->findSearchableRelationshipRow($dataType->rows->where('type', 'relationship'), $search->key)) {
                    $query->whereIn(
                        $searchField,
                        $row->details->model::where($row->details->label, $search_filter, $search_value)->pluck('id')->toArray()
                    );
                } else {
                    if ($dataType->browseRows->pluck('field')->contains($search->key)) {
                        $query->where($searchField, $search_filter, $search_value);
                    }
                }
            }

            $row = $dataType->rows->where('field', $orderBy)->firstWhere('type', 'relationship');
            if ($orderBy && (in_array($orderBy, $dataType->fields()) || !empty($row))) {
                $querySortOrder = (!empty($sortOrder)) ? $sortOrder : 'desc';
                if (!empty($row)) {
                    $query->select([
                        $dataType->name.'.*',
                        'joined.'.$row->details->label.' as '.$orderBy,
                    ])->leftJoin(
                        $row->details->table.' as joined',
                        $dataType->name.'.'.$row->details->column,
                        'joined.'.$row->details->key
                    );
                }

                $dataTypeContent = call_user_func([
                    $query->orderBy($orderBy, $querySortOrder),
                    $getter,
                ]);
            } elseif ($model->timestamps) {
                $dataTypeContent = call_user_func([$query->latest($model::CREATED_AT), $getter]);
            } else {
                $dataTypeContent = call_user_func([$query->orderBy($model->getKeyName(), 'DESC'), $getter]);
            }

            // Replace relationships' keys for labels and create READ links if a slug is provided.
            $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType);
        } else {
            // If Model doesn't exist, get data from table name
            $dataTypeContent = call_user_func([DB::table($dataType->name), $getter]);
            $model = false;
        }

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($model);

        // Eagerload Relations
        $this->eagerLoadRelations($dataTypeContent, $dataType, 'browse', $isModelTranslatable);

        // Check if server side pagination is enabled
        $isServerSide = isset($dataType->server_side) && $dataType->server_side;

        // Check if a default search key is set
        $defaultSearchKey = $dataType->default_search_key ?? null;

        // Actions
        $actions = [];
        if (!empty($dataTypeContent->first())) {
            foreach (Voyager::actions() as $action) {
                $action = new $action($dataType, $dataTypeContent->first());

                if ($action->shouldActionDisplayOnDataType()) {
                    $actions[] = $action;
                }
            }
        }

        // Define showCheckboxColumn
        $showCheckboxColumn = false;
        if (Auth::user()->can('delete', app($dataType->model_name))) {
            $showCheckboxColumn = true;
        } else {
            foreach ($actions as $action) {
                if (method_exists($action, 'massAction')) {
                    $showCheckboxColumn = true;
                }
            }
        }

        // Define orderColumn
        $orderColumn = [];
        if ($orderBy) {
            $index = $dataType->browseRows->where('field', $orderBy)->keys()->first() + ($showCheckboxColumn ? 1 : 0);
            $orderColumn = [[$index, $sortOrder ?? 'desc']];
        }

        // Define list of columns that can be sorted server side
        $sortableColumns = $this->getSortableColumns($dataType->browseRows);

        $view = 'voyager::bread.browse';

        if (view()->exists("voyager::$slug.browse")) {
            $view = "voyager::$slug.browse";
        }

        return Voyager::view($view, compact(
            'actions',
            'dataType',
            'dataTypeContent',
            'isModelTranslatable',
            'search',
            'orderBy',
            'orderColumn',
            'sortableColumns',
            'sortOrder',
            'searchNames',
            'isServerSide',
            'defaultSearchKey',
            'usesSoftDeletes',
            'showSoftDeleted',
            'showCheckboxColumn'
        ));
    }

    public function show(Request $request, $id)
    {
        $slug = 'reports';
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
        $dataTypeContent = call_user_func([app($dataType->model_name)->query(), 'findOrFail'], $id);
        $this->authorize('read', $dataTypeContent);
        return Voyager::view("voyager::$slug.read", compact('dataType', 'dataTypeContent'));
    }

    public function reportProcess(Request $request, $downloadable = false,$printBlade = 'vendor.voyager.reports.print.index',$data=null, $debug=false){
        ini_set("pcre.backtrack_limit", "5000000"); //todo add it in __construct
        $title = $request->get('title');
        $reportType = $request->get('report_type');
        $filter = $request->get('filter');
        if(!empty($filter)){
            $filter = 'Filters: '.$filter;
        }else{
            $filter = '';
        }
        $fileName = str_replace(' ', '_', $title).'_'.date('Y_m_d_h_i_a');

        try{
            if($reportType == 'PDF-VIEW'){
                $pdf = \PDF::loadView($printBlade,
                    [
                        'title'=> $title,
                        'filter' => $filter,
                        'data' => $data,
                        'report_type'=>$reportType
                    ], [],
                    [
                        'format' => 'A4-L' //A4, A4-L, A5-L
                    ]
                );
                return $pdf->stream($fileName.'.pdf');

            }elseif($reportType == 'PDF-DOWNLOAD'){
                $pdf = \PDF::loadView($printBlade,
                    [
                        'title'=> $title,
                        'filter' => $filter,
                        'data' => $data,
                        'report_type'=>$reportType
                    ], [],
                    [
                        'format' => 'A4-L' //A4, A4-L, A5-L
                    ]
                );
                return $pdf->download($fileName.'.pdf');

            }elseif($reportType == 'EXCEL' | $reportType == 'CSV'){
                $dataContent = [
                    [
                        'id', 'name'
                    ],
                ];
                if($data){
                    foreach($data as $d){
                        $dataContent[] = [
                            $d->id,
                            $d->name,
                        ];
                    }
                }
                return SimpleXLSXGen::fromArray( $dataContent )->downloadAs($fileName.'.xlsx');

                /*if($reportType == 'XLS' ){
                    return \SimpleXLSXGen::fromArray( $dataContent )->downloadAs($fileName.'.csv');
                }

                if($reportType == 'CSV'){
                    header('Content-Type: application/csv');
                    header('Content-Disposition: attachment;filename="your_name.csv"');
                    return \SimpleCSV::export( $dataContent);
                }*/
            }else{
                return view($printBlade, [
                    'title'=> !empty($title)?$title:'Report',
                    'filter' => $filter,
                    'data' => $data,
                    'report_type'=>$reportType
                ]);
            }

        }catch (Exception $e){
            echo $e->getMessage();
        }
        return false;
    }

    public function supplierLedger_bk_test(Request $request){
        try{
            $data = Product::select(['id', 'name'])->get();
           return  $this->reportProcess( $request, false, 'vendor.voyager.reports.print.index',$data, false);
        }catch (Exception $e){
            dd($e->getMessage());
        }
    }


    public function customerLedger(Request $request){
        try{
            if($request->customer_id && $data = Customer::where('id',$request->customer_id)->first()){
                return  $this->reportProcess( $request, false, 'vendor.voyager.reports.print.customer-ledger',$data, false);
            }else{
                echo '<h1 style="text-align: center; color: red; padding-top: 20%">Sorry! The requested information could not found.</h1>';
            }
        }catch (Exception $e){
            dd($e->getMessage());
        }
    }

    public function profitLoss(Request $request){
        echo '<h1 style="text-align: center; color: red; padding-top: 20%">Sorry! Under development.</h1>';
        try{
            if($request->customer_id && $data = Customer::where('id',$request->customer_id)->first()){
                return  $this->reportProcess( $request, false, 'vendor.voyager.reports.print.customer-ledger',$data, false);
            }else{
                echo '<h1 style="text-align: center; color: red; padding-top: 20%">Sorry! The requested information could not found.</h1>';
            }
        }catch (Exception $e){
            dd($e->getMessage());
        }
    }

    public function sales(Request $request){
        $customer_id = $request->get('customer_id');
        $from_date = $request->get('from_date');
        $to_date = $request->get('to_date');
        $product_id = $request->get('product_id');
        $sales_type = $request->get('sales_type');

        $data = Sale::where('sales_type', $sales_type);
        if($customer_id){
            $data = $data->where('customer_id', $customer_id);
        }
        if($to_date & $from_date){
            $data = $data->where('sales_date', '>=', $from_date)->where('sales_date','<=', $to_date);
        }
        $data = $data->get();
        if($data){
            return  $this->reportProcess( $request, false, 'vendor.voyager.reports.print.sales',$data, false);
        }else{
            echo '<h1 style="text-align: center; color: red; padding-top: 20%">Sorry! The requested information could not found.</h1>';
        }

    }

    public function purchase(Request $request){
        $supplier_id = $request->get('supplier_id');
        $from_date = $request->get('from_date');
        $to_date = $request->get('to_date');
        $product_id = $request->get('product_id');
        $sales_type = $request->get('sales_type');

        $data = Purchase::orderBy('id', 'desc');
        if($supplier_id){
            $data = $data->where('supplier_id', $supplier_id);
        }
        if($to_date & $from_date){
            $data = $data->where('purchase_date', '>=', $from_date)->where('purchase_date','<=', $to_date);
        }
        $data = $data->get();
        if($data){
            return  $this->reportProcess( $request, false, 'vendor.voyager.reports.print.purchase',$data, false);
        }else{
            echo '<h1 style="text-align: center; color: red; padding-top: 20%">Sorry! The requested information could not found.</h1>';
        }

    }

    public function products(Request $request){
        $product_id = $request->get('product_id');

        $data = Product::orderBy('id', 'desc');
        if($product_id){
            $data = $data->where('id', $product_id);
        }
        $data = $data->get();
        if($data){
            return  $this->reportProcess( $request, false, 'vendor.voyager.reports.print.product',$data, false);
        }else{
            echo '<h1 style="text-align: center; color: red; padding-top: 20%">Sorry! The requested information could not found.</h1>';
        }
    }

    public function stock(Request $request){
        $product_id = $request->get('product_id');

        $data = Stock::orderBy('id', 'desc');
        if($product_id){
            $data = $data->where('product_id', $product_id);
        }
        $data = $data->get();
        if($data){
            return  $this->reportProcess( $request, false, 'vendor.voyager.reports.print.stock',$data, false);
        }else{
            echo '<h1 style="text-align: center; color: red; padding-top: 20%">Sorry! The requested information could not found.</h1>';
        }
    }

    public function agentLedger(Request $request){
        try{
            if($request->agent_id && $data = Agent::where('id',$request->agent_id)->first()){
                return  $this->reportProcess( $request, false, 'vendor.voyager.reports.print.agent-ledger',$data, false);
            }else{
                echo '<h1 style="text-align: center; color: red; padding-top: 20%">Sorry! The requested information could not found.</h1>';
            }
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }

    public function agentReport(Request $request){
        try{
            $query = "SELECT *,
       (select count(*) from candidates where agent_id = a.id) as 'total_candidate',
       (select count(*) from candidates where class_group_id = 1 and agent_id = a.id) as 'admission_cand',
       (select count(*) from candidates where class_group_id in (select id from class_groups where name like '%Final%') and agent_id = a.id) as 'final_group',
       0 as 'selected_cand',
       0 as 'visa_updated_cand',
       0 as 'payment_due_cand',
       0 as 'balance',
       0 as 'due_balance'
FROM agents a where status = 'A'
ORDER BY name asc";
            if($data = \DB::select($query)){
                return  $this->reportProcess( $request, false, 'vendor.voyager.reports.print.agent-report',$data, false);
            }else{
                echo '<h1 style="text-align: center; color: red; padding-top: 20%">Sorry! The requested information could not found.</h1>';
            }
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }

    public function selectedCandidateReport(Request $request){
        try{

            if($data = Candidate::where('status' , 'A')->orderBy('name', 'asc')->get()){
                return  $this->reportProcess( $request, false, 'vendor.voyager.reports.print.selected-candidate-report',$data, false);
            }else{
                echo '<h1 style="text-align: center; color: red; padding-top: 20%">Sorry! The requested information could not found.</h1>';
            }
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }

    public function flightReport(Request $request){
        try{

            if($data = Candidate::where('status' , 'A')->orderBy('name', 'asc')->get()){
                return  $this->reportProcess( $request, false, 'vendor.voyager.reports.print.flight-report',$data, false);
            }else{
                echo '<h1 style="text-align: center; color: red; padding-top: 20%">Sorry! The requested information could not found.</h1>';
            }
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }

    public function employerReport(Request $request){
        try{

            if($data = Candidate::where('status' , 'A')->orderBy('name', 'asc')->get()){
                return  $this->reportProcess( $request, false, 'vendor.voyager.reports.print.employer-report',$data, false);
            }else{
                echo '<h1 style="text-align: center; color: red; padding-top: 20%">Sorry! The requested information could not found.</h1>';
            }
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }

    public function examReport(Request $request){
        try{

            if($data = Candidate::where('status' , 'A')->orderBy('name', 'asc')->get()){
                return  $this->reportProcess( $request, false, 'vendor.voyager.reports.print.exam-report',$data, false);
            }else{
                echo '<h1 style="text-align: center; color: red; padding-top: 20%">Sorry! The requested information could not found.</h1>';
            }
        }catch (Exception $e){
            echo $e->getMessage();
        }
    }





}
