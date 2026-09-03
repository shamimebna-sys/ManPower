<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonClass;
use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\Candidate;
use App\Models\ClassGroup;
use App\Models\Notification;
use App\Models\SubAgent;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Events\BreadDataUpdated;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as VoyagerBaseController;
use TCG\Voyager\Models\User;

class VoyagerCandidatesController extends VoyagerBaseController
{
    private $role_id = 102;


    public function index(Request $request)
    {
        ini_set('max_execution_time', 180);
        ini_set('memory_limit', '512M');

        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);

        // GET THE DataType based on the slug
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        $getter = $dataType->server_side ? 'paginate' : 'get';

        $search = (object) [
            'value' => $request->get('s'),
            'key' => $request->get('key'),
            'filter' => $request->get('filter'),
            'search_group'=>$request->search_group,
            'search_agent'=>$request->search_agent,
            'search_agency'=>$request->search_agency,
            'search_license_status'=>$request->search_license_status,
            'search_company'=>$request->search_company
        ];

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

            $query = $model::select($dataType->name.'.*')->with(['agency', 'company', 'replacedByCandidate', 'replacesCandidate']);

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


            $query->when($request->search_group, fn($q) =>
                $q->where('class_group_id', $request->search_group)
            );

            $query->when($request->search_agent, fn($q) =>
            $q->where('agent_id', $request->search_agent)
            );

            $query->when($request->search_agency, fn($q) =>
            $q->where('agencier_id', $request->search_agency)
            );

            $query->when($request->search_license_status, fn($q) =>
            $q->where('position_id', $request->search_license_status)
            );
			
            $query->when($request->search_company, fn($q) =>
            $q->where('companier_id', $request->search_company)
            );


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

    public function store(Request $request)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        $request->merge(['code' => CommonClass::generateCode('C', $request->get('agent_id'))]);
        $request->merge(['email' => strtolower($request->get('email'))]);
        $validator = Validator::make($request->all(), [
                'code' => 'required|unique:candidates,code',
                'name' => 'required',
                'mobile' => 'required|unique:candidates,mobile',
                'email' => [
                    'required',
                    'email',
                    'unique:users,email',
                    'unique:candidates,email'
                ],
                'passport_no' => 'required|unique:candidates,passport_no',
                'class_group_id' => 'required',
                'agent_id' => 'required',
            ],[
                'class_group_id.required'=>'Group is required',
                'agent_id.required'=>'Agent is required',
            ]
        );
        if ($validator->fails()) {
            foreach ($validator->messages()->getMessages() as $messages) {
                return redirect()->back()->with($this->alertError(__($messages[0])))->withInput();
            }
        }


        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows)->validate();
        $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());

        // Sync candidate replacement relationship
        $this->syncReplacements($data, $request);

        event(new BreadDataAdded($dataType, $data));


        //create user
        $password =  !empty($request->password)?$request->password:'password';
        $user = User::create([
            'status'           => $data->status,
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => bcrypt($password),
            'remember_token' => Str::random(60),
            'role_id'        => $this->role_id,
            'user_type_id'        => $this->role_id,
            'candidate_id'        => $data->id,
            'avatar'        => 'users/default.png'
        ]);

        if($user && !empty($password) && !empty($request->email)){
            // Send notifications and emails
            CommonClass::sendTargetedNotificationAndEmail('candidate', $data->id, 'Candidate Registration', 'Welcome! Your candidate profile has been registered successfully. Candidate Code: ' . $data->code);

            if (!empty($data->agent_id)) {
                CommonClass::sendTargetedNotificationAndEmail('agent', $data->agent_id, 'New Candidate Added', 'A new candidate, ' . $data->name . ' (Code: ' . $data->code . '), has been registered under your agency.');
            }

            if (!empty($data->sub_agent_id)) {
                CommonClass::sendTargetedNotificationAndEmail('sub_agent', $data->sub_agent_id, 'New Candidate Added', 'A new candidate, ' . $data->name . ' (Code: ' . $data->code . '), has been registered under your sub-agency.');
            }

            CommonClass::sendTargetedNotificationAndEmail('owner', null, 'New Candidate Added', 'A new candidate, ' . $data->name . ' (Code: ' . $data->code . '), has been registered in the system.');

            // Check if any documents were uploaded
            $fileFields = [
                'bid_file_path', 'nid_file_path', 'passport_file_path',
                'full_photo_file_path', 'half_photo_file_path', 'cv_file_path',
                'skill_certificate_file_path', 'stamp_file_path'
            ];
            $documentAttached = false;
            foreach ($fileFields as $field) {
                if ($request->hasFile($field) || !empty($data->{$field})) {
                    $documentAttached = true;
                    break;
                }
            }
            if ($documentAttached) {
                if (!empty($data->agent_id)) {
                    CommonClass::sendTargetedNotificationAndEmail('agent', $data->agent_id, 'Candidate Document Attached', 'A new document has been attached to candidate ' . $data->name . ' (Code: ' . $data->code . ').');
                }
                if (!empty($data->sub_agent_id)) {
                    CommonClass::sendTargetedNotificationAndEmail('sub_agent', $data->sub_agent_id, 'Candidate Document Attached', 'A new document has been attached to candidate ' . $data->name . ' (Code: ' . $data->code . ').');
                }
            }

            CommonClass::sendEmail('Candidate Registration', $request->email, ['username'=>$request->email, 'password'=>$password], 'email.registration');
        }



        if (!$request->has('_tagging')) {
            if (auth()->user()->can('browse', $data)) {
                $redirect = redirect()->route("voyager.{$dataType->slug}.index");
            } else {
                $redirect = redirect()->back();
            }

            return $redirect->with([
                'message'    => __('voyager::generic.successfully_added_new')." {$dataType->getTranslatedAttribute('display_name_singular')}". ' And Code is '.$data->code,
                'alert-type' => 'success',
            ]);
        } else {
            return response()->json(['success' => true, 'data' => $data]);
        }
    }

    public function update(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Compatibility with Model binding.
        $id = $id instanceof \Illuminate\Database\Eloquent\Model ? $id->{$id->getKeyName()} : $id;

        $request->merge(['email' => strtolower($request->get('email'))]);
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mobile' => 'required|unique:candidates,mobile,' .$id,
            'email' => [
                'required',
                'email',
                'unique:users,email,' . User::where('candidate_id', $id)->pluck('id')->first(),
                'unique:candidates,email,' . $id
            ],
            'passport_no' => 'required|unique:candidates,passport_no,' . $id,
            'class_group_id' => 'required',
            'agent_id' => 'required',
        ],[
                'class_group_id.required'=>'Group is required',
                'agent_id.required'=>'Agent is required',
            ]
        );
        if ($validator->fails()) {
            foreach ($validator->messages()->getMessages() as $messages) {
                return redirect()->back()->with($this->alertError(__($messages[0])))->withInput();
            }
        }



        $model = app($dataType->model_name);
        $query = $model->query();
        if ($dataType->scope && $dataType->scope != '' && method_exists($model, 'scope'.ucfirst($dataType->scope))) {
            $query = $query->{$dataType->scope}();
        }
        if ($model && in_array(SoftDeletes::class, class_uses_recursive($model))) {
            $query = $query->withTrashed();
        }

        $data = $query->findOrFail($id);

        // Check permission
        $this->authorize('edit', $data);

        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->editRows, $dataType->name, $id)->validate();

        // Get fields with images to remove before updating and make a copy of $data
        $to_remove = $dataType->editRows->where('type', 'image')
            ->filter(function ($item, $key) use ($request) {
                return $request->hasFile($item->field);
            });
        $original_group_id = $data->class_group_id;
        $original_data = clone($data);

        $this->insertUpdateData($request, $slug, $dataType->editRows, $data);

        // Sync candidate replacement relationship
        $this->syncReplacements($data, $request);

        // Delete Images
        $this->deleteBreadImages($original_data, $to_remove);

        event(new BreadDataUpdated($dataType, $data));

        //update user
        $userEntity = User::where('candidate_id', $data->id)->first();
        $userEntity->email = $request->get('email');
        $userEntity->status = $data->status;
        if(!empty($request->password)){
            $userEntity->password = bcrypt($request->password);
        }
        $userEntity->update();

        // Group change check
        if ($original_group_id != $data->class_group_id) {
            $newGroup = \App\Models\ClassGroup::find($data->class_group_id);
            $newGroupName = $newGroup ? $newGroup->name : 'N/A';

            CommonClass::sendTargetedNotificationAndEmail('candidate', $data->id, 'Group Changed', 'Your class group has been updated to ' . $newGroupName . '.');

            if (!empty($data->agent_id)) {
                CommonClass::sendTargetedNotificationAndEmail('agent', $data->agent_id, 'Candidate Group Changed', 'Candidate ' . $data->name . ' (Code: ' . $data->code . ') has been moved to group ' . $newGroupName . '.');
            }

            if (!empty($data->sub_agent_id)) {
                CommonClass::sendTargetedNotificationAndEmail('sub_agent', $data->sub_agent_id, 'Candidate Group Changed', 'Candidate ' . $data->name . ' (Code: ' . $data->code . ') has been moved to group ' . $newGroupName . '.');
            }

            CommonClass::sendTargetedNotificationAndEmail('owner', null, 'Candidate Group Changed', 'Candidate ' . $data->name . ' (Code: ' . $data->code . ') has been moved to group ' . $newGroupName . '.');
        }

        // Document change check
        $fileFields = [
            'bid_file_path', 'nid_file_path', 'passport_file_path',
            'full_photo_file_path', 'half_photo_file_path', 'cv_file_path',
            'skill_certificate_file_path', 'stamp_file_path'
        ];
        $documentAttached = false;
        foreach ($fileFields as $field) {
            if ($request->hasFile($field) || ($data->{$field} !== $original_data->{$field} && !empty($data->{$field}))) {
                $documentAttached = true;
                break;
            }
        }
        if ($documentAttached) {
            if (!empty($data->agent_id)) {
                CommonClass::sendTargetedNotificationAndEmail('agent', $data->agent_id, 'Candidate Document Attached', 'A new document has been attached to candidate ' . $data->name . ' (Code: ' . $data->code . ').');
            }
            if (!empty($data->sub_agent_id)) {
                CommonClass::sendTargetedNotificationAndEmail('sub_agent', $data->sub_agent_id, 'Candidate Document Attached', 'A new document has been attached to candidate ' . $data->name . ' (Code: ' . $data->code . ').');
            }
        }



        if (auth()->user()->can('browse', app($dataType->model_name))) {
            $redirect = redirect()->route("voyager.{$dataType->slug}.index");
        } else {
            $redirect = redirect()->back();
        }

        return $redirect->with([
            'message'    => __('voyager::generic.successfully_updated')." {$dataType->getTranslatedAttribute('display_name_singular')}",
            'alert-type' => 'success',
        ]);
    }

    public function relation(Request $request)
    {
        if (in_array($request->type, ['selected_candidate_belongsto_candidate_relationship'])) {

            $query =  Candidate::with(['classGroup', 'latestExamResult'])->where('status', 'A')
                ->whereHas('classGroup', function ($query) {
                    $query->where('name', 'like', '%Rapid%');
                });


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

        //load sub agent by agent id and search sub agent
        if (in_array($request->type, ['candidate_belongsto_sub_agent_relationship'])) {

          //  dd($request->all());

            $candidate_id = $request->get('id');
            $agent_id = $request->get('agent_id');

            if(!empty($agent_id)){
                $query = SubAgent::where('agent_id', $agent_id);
            }else{
                $candidate = Candidate::where('id',$candidate_id )->first();
                if($candidate){
                    $query = SubAgent::where('agent_id',  $candidate->agent_id);
                }else{
                    $query = SubAgent::where('id',  0);
                }
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

    /**
     * AJAX search endpoint for candidates replacement dropdowns
     * Added for company candidate replacement feature
     */
    public function searchCandidates(Request $request)
    {
        $search = $request->get('q');
        $excludeId = $request->get('exclude_id');

        $candidates = Candidate::query()
            ->when($search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('code', 'LIKE', "%{$search}%")
                      ->orWhere('passport_no', 'LIKE', "%{$search}%")
                      ->orWhere('mobile', 'LIKE', "%{$search}%");
                });
            })
            ->when($excludeId, function ($query, $excludeId) {
                $query->where('id', '!=', $excludeId);
            })
            ->select('id', 'name', 'code', 'passport_no')
            ->limit(20)
            ->get();

        $results = [];
        foreach ($candidates as $candidate) {
            $results[] = [
                'id' => $candidate->id,
                'text' => "({$candidate->code}) {$candidate->name} - {$candidate->passport_no}"
            ];
        }

        return response()->json(['results' => $results]);
    }

    /**
     * Helper to synchronize candidate replacement references bidirectionally
     * Added for company candidate replacement feature
     */
    private function syncReplacements($candidate, Request $request)
    {
        $oldReplacedById = $candidate->replaced_by_candidate_id;

        // If replaced_by_candidate_id was submitted in request
        if ($request->has('replaced_by_candidate_id')) {
            $newReplacedById = $request->replaced_by_candidate_id ? (int)$request->replaced_by_candidate_id : null;

            if ($oldReplacedById != $newReplacedById) {
                // Set the link on current candidate (Candidate A)
                $candidate->replaced_by_candidate_id = $newReplacedById;

                if ($newReplacedById) {
                    // Candidate B is the replacement candidate
                    $candidateB = Candidate::find($newReplacedById);

                    if ($candidateB) {
                        // 1. Candidate B inherits Candidate A's company details
                        if ($candidate->companier_id) {
                            $candidateB->companier_id = $candidate->companier_id;

                            // Re-assign database records in 'companies' table from A to B
                            DB::table('companies')
                                ->where('candidate_id', $candidate->id)
                                ->update(['candidate_id' => $candidateB->id]);

                            // Clear Candidate A's company details
                            $candidate->companier_id = null;
                        }

                        // 2. Write status notes to replacement_remarks for audit trail
                        $aNote = "Status Note: Replaced by Candidate " . $candidateB->code . " (" . $candidateB->name . ") at ".date('Y-m-d H:i:s') ;
                        $candidate->replacement_remarks = $candidate->replacement_remarks
                            ? $candidate->replacement_remarks . " | " . $aNote
                            : $aNote;

                        $bNote = "Status Note: Replaces Candidate " . $candidate->code . " (" . $candidate->name . ") at ".date('Y-m-d H:i:s');
                        $candidateB->replacement_remarks = $candidateB->replacement_remarks
                            ? $candidateB->replacement_remarks . " | " . $bNote
                            : $bNote;

                        $candidateB->save();
                    }
                } else {
                    // Replacement cleared
                    if ($oldReplacedById) {
                        $candidateOldB = Candidate::find($oldReplacedById);
                        if ($candidateOldB) {
                            $aNote = "Status Note: Replacement candidate " . $candidateOldB->code . " was unlinked.  at ".date('Y-m-d H:i:s') ;
                            $candidate->replacement_remarks = $candidate->replacement_remarks
                                ? $candidate->replacement_remarks . " | " . $aNote
                                : $aNote;
                        }
                    }
                }

                $candidate->save();
            }
        }
    }
}
