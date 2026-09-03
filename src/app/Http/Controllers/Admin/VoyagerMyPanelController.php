<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonClass;
use App\Models\Agent;
use App\Models\AttributeList;
use App\Models\Candidate;
use App\Models\Currency;
use App\Models\CurricularList;
use App\Models\Education;
use App\Models\ExpertiseList;
use App\Models\Exprience;
use App\Models\InterestList;
use App\Models\LanguageList;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PaymentRequest;
use App\Models\SubAgentPaymentRequest;
use App\Models\Skill;
use App\Models\SkillList;
use App\Models\SubAgent;
use App\Models\Teacher;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use niklasravnsborg\LaravelPdf\Pdf;
use PHPUnit\Exception;
use Shuchkin\SimpleXLSXGen;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Events\BreadDataUpdated;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as VoyagerBaseController;
use TCG\Voyager\Models\User;

class VoyagerMyPanelController extends VoyagerBaseController
{

    public function profile(Request $request )
    {
        $expriences = null;
        $educations = null;
        $skills = null;
        $trainings = null;

        $user = \Auth::user();
        if($user->role->id == 101){
            $id = \Auth::user()->agent_id;
            $slug = 'agents';
        }elseif($user->role->id == 102){
            $id = \Auth::user()->candidate_id;
            $slug = 'candidates';

            $languageList = LanguageList::where('user_id', $user->id)->get();
            $expertiseList = ExpertiseList::where('user_id', $user->id)->get();
            $skillList = SkillList::where('user_id', $user->id)->get();
            $curricularList = CurricularList::where('user_id', $user->id)->get();
            $interestList = InterestList::where('user_id', $user->id)->get();
            $attributeList = AttributeList::where('user_id', $user->id)->get();

            $expriences = Exprience::where('user_id', $user->id)->get();
            $educations = Education::where('user_id', $user->id)->get();
            $skills = Skill::where('user_id', $user->id)->get();
            $trainings = Training::where('user_id', $user->id)->get();

        }elseif($user->role->id == 103){
            $id = \Auth::user()->teacher_id;
            $slug = 'teachers';
        }elseif($user->role->id == 104){
            $id = \Auth::user()->employee_id;
            $slug = 'employees';
        }elseif($user->role->id == 105){
            $id = \Auth::user()->employer_id;
            $slug = 'employers';
        }else{
            return  redirect()->route('voyager.profile');
        }


        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);
            $query = $model->query();
            if ($dataType->scope && $dataType->scope != '' && method_exists($model, 'scope'.ucfirst($dataType->scope))) {
                $query = $query->{$dataType->scope}();
            }
            $dataTypeContent = call_user_func([$query, 'findOrFail'], $id);
        } else {
            $dataTypeContent = DB::table($dataType->name)->where('id', $id)->first();
        }
        foreach ($dataType->editRows as $key => $row) {
            $dataType->editRows[$key]['col_width'] = isset($row->details->width) ? $row->details->width : 100;
        }

//        dd($dataTypeContent);

        return view('voyager::my.profile-view', compact('dataType', 'dataTypeContent',
            'languageList', 'expertiseList', 'skillList', 'curricularList', 'interestList', 'attributeList',
            'expriences', 'trainings', 'skills', 'educations' ));
//        return view('voyager::my.profile', compact('dataType', 'dataTypeContent', 'expriences', 'trainings', 'skills', 'educations' ));
    }

    public function profileEdit(Request $request)
    {
        $expriences = null;
        $educations = null;
        $skills = null;
        $trainings = null;

        $user = \Auth::user();
        if($user->role->id == 101){
            $id = \Auth::user()->agent_id;
            $slug = 'agents';
        }elseif($user->role->id == 102){
            $id = \Auth::user()->candidate_id;
            $slug = 'candidates';

            $languageList = LanguageList::where('user_id', $user->id)->get();
            $expertiseList = ExpertiseList::where('user_id', $user->id)->get();
            $skillList = SkillList::where('user_id', $user->id)->get();
            $curricularList = CurricularList::where('user_id', $user->id)->get();
            $interestList = InterestList::where('user_id', $user->id)->get();
            $attributeList = AttributeList::where('user_id', $user->id)->get();

            $expriences = Exprience::where('user_id', $user->id)->get();
            $educations = Education::where('user_id', $user->id)->get();
            $skills = Skill::where('user_id', $user->id)->get();
            $trainings = Training::where('user_id', $user->id)->get();

        }elseif($user->role->id == 103){
            $id = \Auth::user()->teacher_id;
            $slug = 'teachers';
        }elseif($user->role->id == 104){
            $id = \Auth::user()->employee_id;
            $slug = 'employees';
        }elseif($user->role->id == 105){
            $id = \Auth::user()->employer_id;
            $slug = 'employers';
        }else{
            return  redirect()->route('voyager.profile');
        }


        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);
            $query = $model->query();
            if ($dataType->scope && $dataType->scope != '' && method_exists($model, 'scope'.ucfirst($dataType->scope))) {
                $query = $query->{$dataType->scope}();
            }
            $dataTypeContent = call_user_func([$query, 'findOrFail'], $id);
        } else {
            $dataTypeContent = DB::table($dataType->name)->where('id', $id)->first();
        }
        foreach ($dataType->editRows as $key => $row) {
            $dataType->editRows[$key]['col_width'] = isset($row->details->width) ? $row->details->width : 100;
        }


//        return view('voyager::my.profile-view', compact('dataType', 'dataTypeContent', 'expriences', 'trainings', 'skills', 'educations' ));
        return view('voyager::my.profile', compact('dataType', 'dataTypeContent',
            'languageList', 'expertiseList', 'skillList', 'curricularList', 'interestList', 'attributeList',
            'expriences', 'trainings', 'skills', 'educations' ));
    }

    public function profileUpdate(Request $request)
    {
       $status  = $request->get('status');
       $code  = $request->get('code');
       $agent_id  = $request->get('agent_id');
       $class_group_id  = $request->get('class_group_id');

        $user = \Auth::user();
        if($user->role->id == 101){
            $id = \Auth::user()->agent_id;
            $slug = 'agents';
        }elseif($user->role->id == 102){
            $id = \Auth::user()->candidate_id;
            $slug = 'candidates';
        }elseif($user->role->id == 103){
            $id = \Auth::user()->teacher_id;
            $slug = 'teachers';
        }elseif($user->role->id == 104){
            $id = \Auth::user()->employee_id;
            $slug = 'employees';
        }elseif($user->role->id == 105){
            $id = \Auth::user()->employer_id;
            $slug = 'employers';
        }

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Compatibility with Model binding.
        $id = $id instanceof \Illuminate\Database\Eloquent\Model ? $id->{$id->getKeyName()} : $id;

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
//        $this->authorize('edit', $data);

        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->editRows, $dataType->name, $id)->validate();

        // Get fields with images to remove before updating and make a copy of $data
        $to_remove = $dataType->editRows->where('type', 'image')
            ->filter(function ($item, $key) use ($request) {
                return $request->hasFile($item->field);
            });
        $original_data = clone($data);

        $this->insertUpdateData($request, $slug, $dataType->editRows, $data);

        // Delete Images
        $this->deleteBreadImages($original_data, $to_remove);

        event(new BreadDataUpdated($dataType, $data));

        //update user
        if(!empty($request->password)){
            $userEntity = User::where('agent_id', $data->id)->first();
            $userEntity->password = bcrypt($request->password);
            $userEntity->update();
        }
        if(!empty($data->half_photo_file_path)){
            $userEntity = User::where('id', Auth::user()->id)->first();
            $userEntity->avatar = $data->half_photo_file_path;
            $userEntity->update();
        }


        //for candidate update
        if($user->role->id == 102){
            $candidateOldData = Candidate::where('id',$id )->first();
            if(empty($status)){
                $request->merge([  'status' => $candidateOldData->status ]);
            }
            if(empty($code)){
                $request->merge([  'code' => $candidateOldData->code ]);
            }
            if(empty($agent_id)){
                $request->merge([  'agent_id' => $candidateOldData->agent_id ]);
            }
            if(empty($class_group_id)){
                $request->merge([  'class_group_id' => $candidateOldData->class_group_id ]);
            }

            //Language list data process
            $language_name = $request->get('language_name');
            $language_status = $request->get('language_status');
            LanguageList::where('user_id', $user->id)->delete();
            if(count($language_name) > 0){
                $languageData = [];
                foreach ($language_name as $i=>$v){
                    if(!empty($language_name[$i])) {
                        $languageData[] = [
                            'user_id' => $user->id,
                            'language_name' => $language_name[$i],
                            'language_status' => $language_status[$i],
                        ];
                    }
                }
                if(count($languageData) > 0){
                    LanguageList::insert($languageData);
                }
            }

            //ExpertiseList list data process
            $expertise_name = $request->get('expertise_name');
            ExpertiseList::where('user_id', $user->id)->delete();
            if(count($expertise_name) > 0){
                $expertiseData = [];
                foreach ($expertise_name as $i=>$v){
                    if(!empty($expertise_name[$i])) {
                        $expertiseData[] = [
                            'user_id' => $user->id,
                            'expertise_name' => $expertise_name[$i],
                        ];
                    }
                }
                if(count($expertiseData) > 0){
                    ExpertiseList::insert($expertiseData);
                }
            }

            //skill_list list data process
            $skill_name = $request->get('skill_name');
            SkillList::where('user_id', $user->id)->delete();
            if(count($skill_name) > 0){
                $skillListData = [];
                foreach ($skill_name as $i=>$v){
                    if(!empty($skill_name[$i])) {
                        $skillListData[] = [
                            'user_id' => $user->id,
                            'skill_name' => $skill_name[$i],
                        ];
                    }
                }
                if(count($skillListData) > 0){
                    SkillList::insert($skillListData);
                }
            }

            //CurricularList list data process
            $curricular_name = $request->get('curricular_name');
            CurricularList::where('user_id', $user->id)->delete();
            if(count($curricular_name) > 0){
                $curricularListData = [];
                foreach ($curricular_name as $i=>$v){
                    if(!empty($curricular_name[$i])) {
                        $curricularListData[] = [
                            'user_id' => $user->id,
                            'curricular_name' => $curricular_name[$i],
                        ];
                    }
                }
                if(count($curricularListData) > 0){
                    CurricularList::insert($curricularListData);
                }
            }

            //$interest_name list data process
            $interest_name = $request->get('interest_name');
            InterestList::where('user_id', $user->id)->delete();
            if(count($interest_name) > 0){
                $interestListData = [];
                foreach ($interest_name as $i=>$v){
                    if(!empty($interest_name[$i])) {
                        $interestListData[] = [
                            'user_id' => $user->id,
                            'interest_name' => $interest_name[$i],
                        ];
                    }
                }
                if(count($interestListData) > 0){
                    InterestList::insert($interestListData);
                }
            }

            //$attribute_name list data process
            $attribute_name = $request->get('attribute_name');
            AttributeList::where('user_id', $user->id)->delete();
            if(count($attribute_name) > 0){
                $attributeListData = [];
                foreach ($attribute_name as $i=>$v){
                    if(!empty($attribute_name[$i])) {
                        $attributeListData[] = [
                            'user_id' => $user->id,
                            'attribute_name' => $attribute_name[$i],
                        ];
                    }
                }
                if(count($attributeListData) > 0){
                    AttributeList::insert($attributeListData);
                }
            }


            //Exprience data process
            $experience_designation = $request->get('experience_designation');
            $experience_company_name = $request->get('experience_company_name');
            $experience_company_address = $request->get('experience_company_address');
            $experience_start_date = $request->get('experience_start_date');
            $experience_end_date = $request->get('experience_end_date');
            $experience_responsibilities = $request->get('experience_responsibilities');

            Exprience::where('user_id', $user->id)->delete();
            if(count($experience_company_name) > 0){
                $exprienceData = [];
                foreach ($experience_designation as $i=>$edu){
                    if(!empty($experience_company_name[$i])){
                        $exprienceData[] = [
                            'user_id' => $user->id,
                            'company_name' => $experience_company_name[$i],
                            'company_address' => $experience_company_address[$i],
                            'designation' => $experience_designation[$i],
                            'start_date' => $experience_start_date[$i],
                            'end_date' => $experience_end_date[$i],
                            'responsibilities' => $experience_responsibilities[$i],
                        ];
                    }

                }

                if(count($exprienceData) > 0){
                    Exprience::insert($exprienceData);
                }
            }

            //Education data process
            $education_exam_name = $request->get('education_exam_name');
            $education_subject_group_major = $request->get('education_subject_group_major');
            $education_education_label = $request->get('education_education_label');
            $education_institute_name = $request->get('education_institute_name');
            $education_board_name = $request->get('education_board_name');
            $education_result = $request->get('education_result');
            $education_scale = $request->get('education_scale');
            $education_passing_year = $request->get('education_passing_year');
            $education_duration_year = $request->get('education_duration_year');

            Education::where('user_id', $user->id)->delete();
            if(count($education_exam_name) > 0){
                $educationData = [];
                foreach ($education_exam_name as $i=>$edu){
                    if(!empty($education_exam_name[$i])) {
                        $educationData[] = [
                            'user_id' => $user->id,
                            'exam_name' => $education_exam_name[$i],
                            'institute_name' => $education_institute_name[$i],
                            'subject_group_major' => $education_subject_group_major[$i],
                            'education_label' => $education_education_label[$i],
                            'duration_year' => $education_duration_year[$i],
                            'result' => $education_result[$i],
                            'board_name' => $education_board_name[$i],
                            'scale' => $education_scale[$i],
                            'passing_year' => $education_passing_year[$i],
                        ];
                    }
                }

                if(count($educationData) > 0){
                    Education::insert($educationData);
                }
            }


            //Training data process
            $training_title = $request->get('training_title');
            $training_training_institute = $request->get('training_training_institute');
            $training_address = $request->get('training_address');
            $training_country_id = $request->get('training_country_id');
            $training_start_date = $request->get('training_start_date');
            $training_end_date = $request->get('training_end_date');
            $training_topics = $request->get('training_topics');

            Training::where('user_id', $user->id)->delete();
            if(count($training_title) > 0){
                $trainingData = [];
                foreach ($training_title as $i=>$edu){
                    if(!empty($training_title[$i])) {
                        $trainingStart = Carbon::parse($training_start_date[$i]);
                        $trainingEnd = Carbon::parse($training_end_date[$i]);
                        $trainingDuration = $trainingStart->diffInYears($trainingEnd);
                        $trainingData[] = [
                            'user_id' => $user->id,
                            'title' => $training_title[$i],
                            'institute_name' => $training_training_institute[$i],
                            'address' => $training_address[$i],
                            'country_id' => $training_country_id[$i],
                            'start_date' => $training_start_date[$i],
                            'end_date' => $training_end_date[$i],
                            'duration' => $trainingDuration,
                            'topics' => $training_topics[$i],
                        ];
                    }
                }

                if(count($trainingData) > 0){
                    Training::insert($trainingData);
                }
            }


            //Skills data process
            $skill_title = $request->get('skill_title');
            $skill_institute_name = $request->get('skill_institute_name');
            $skill_result_score = $request->get('skill_result_score');
            $skill_exam_score = $request->get('skill_exam_score');
            $skill_details = $request->get('skill_details');

            Skill::where('user_id', $user->id)->delete();
            if(count($skill_title) > 0){
                $skillData = [];
                foreach ($skill_title as $i=>$edu){
                    if(!empty($skill_title[$i])) {
                        $skillData[] = [
                            'user_id' => $user->id,
                            'title' => $skill_title[$i],
                            'institute_name' => $skill_institute_name[$i],
                            'result_score' => $skill_result_score[$i],
                            'exam_score' => $skill_exam_score[$i],
                            'details' => $skill_details[$i],
                        ];
                    }
                }

                if(count($skillData) > 0){
                    Skill::insert($skillData);
                }
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

    public function profileExport(Request $request){
        $expriences = null;
        $educations = null;
        $skills = null;
        $trainings = null;

        $user = \Auth::user();
        if($user->role->id == 101){
            $id = \Auth::user()->agent_id;
            $slug = 'agents';
        }elseif($user->role->id == 102){
            $id = \Auth::user()->candidate_id;
            $slug = 'candidates';

            $languageList = LanguageList::where('user_id', $user->id)->get();
            $expertiseList = ExpertiseList::where('user_id', $user->id)->get();
            $skillList = SkillList::where('user_id', $user->id)->get();
            $curricularList = CurricularList::where('user_id', $user->id)->get();
            $interestList = InterestList::where('user_id', $user->id)->get();
            $attributeList = AttributeList::where('user_id', $user->id)->get();

            $expriences = Exprience::where('user_id', $user->id)->get();
            $educations = Education::where('user_id', $user->id)->get();
            $skills = Skill::where('user_id', $user->id)->get();
            $trainings = Training::where('user_id', $user->id)->get();

        }elseif($user->role->id == 103){
            $id = \Auth::user()->teacher_id;
            $slug = 'teachers';
        }elseif($user->role->id == 104){
            $id = \Auth::user()->employee_id;
            $slug = 'employees';
        }elseif($user->role->id == 105){
            $id = \Auth::user()->employer_id;
            $slug = 'employers';
        }else{
            return  redirect()->route('voyager.profile');
        }


        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);
            $query = $model->query();
            if ($dataType->scope && $dataType->scope != '' && method_exists($model, 'scope'.ucfirst($dataType->scope))) {
                $query = $query->{$dataType->scope}();
            }
            $dataTypeContent = call_user_func([$query, 'findOrFail'], $id);
        } else {
            $dataTypeContent = DB::table($dataType->name)->where('id', $id)->first();
        }
        foreach ($dataType->editRows as $key => $row) {
            $dataType->editRows[$key]['col_width'] = isset($row->details->width) ? $row->details->width : 100;
        }

        $downloadable = false;
        $printBlade = 'voyager::my.profile-export';
        $debug=true;

        ini_set("pcre.backtrack_limit", "5000000"); //todo add it in __construct
        $title = 'my_resume';
        $reportType = 'PDF';
        $fileName = str_replace(' ', '_', $title).'_'.date('Y_m_d_h_i_a');

        try{
            if($debug){
                return view($printBlade, [
                    'title'=> 'CV',
                    'dataTypeContent'=>$dataTypeContent,
                    'expriences'=>$expriences,
                    'trainings'=>$trainings,
                    'skills'=>$skills,
                    'educations'=>$educations,
                    'languageList'=>$languageList,
                    'expertiseList'=>$expertiseList,
                    'skillList'=>$skillList,
                    'curricularList'=>$curricularList,
                    'interestList'=>$interestList,
                    'attributeList'=>$attributeList,


                ]);
            }


            if($reportType == 'PDF'){
                $pdf = \PDF::loadView($printBlade,
                    [
                        'title'=> $title,
                        'dataTypeContent'=>$dataTypeContent,
                        'expriences'=>$expriences,
                        'trainings'=>$trainings,
                        'skills'=>$skills,
                        'educations'=>$educations
                    ], [],
                    [
                        'format' => 'A4' //A4, A4-L, A5-L
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

    public function wallet(Request $request)
    {
        $currencies = Currency::all();
        if(CommonClass::user()->role_id == 101){
            $info = Agent::where('id',CommonClass::user()->profile_id)->first();

        }elseif(CommonClass::user()->role_id == 109){
            $info = SubAgent::where('id',CommonClass::user()->profile_id)->first();
        }else{
            $info = Agent::where('id',0)->first(); // for invalid user
        }
		//dd($info);
        return Voyager::view('voyager::my.wallet', ['info'=>$info, 'currencies'=>$currencies]);
    }

    public function walletStore (Request $request)
    {
        $validationRules = [
            'invoice_no' => 'required',
            'payment_date' => 'required|before_or_equal:today',
            'payment_time' => 'required',
            'amount' => 'required|numeric|min:1',
        ];

        $payment_method = $request->get('payment_method');
        if($payment_method == 'CASH'){
            $validationRules['payment_receiver'] = 'required';
            $validationRules['payment_received_location'] = 'required';
        }else{
            $validationRules['bank_name'] = 'required';
            $validationRules['account_name'] = 'required';
            $validationRules['acc_no'] = 'required';
            $validationRules['trx_no'] = 'required';
            if (is_array($request->file('cheque_file_path'))) {
                $validationRules['cheque_file_path'] = 'required|array';
                $validationRules['cheque_file_path.*'] = 'file|mimetypes:image/*,application/pdf|max:10240';
            } else {
                $validationRules['cheque_file_path'] = 'required|file|mimetypes:image/*,application/pdf|max:10240';
            }
        }

//        dd($validationRules);
        $validatinMsg = [
            'payment_date.before_or_equal' => 'The Payment date must be today or a past date.',
            'cheque_file_path.required' => 'Payment document i.e cheque or photo or pdf is required.',
            'cheque_file_path.file' => 'The uploaded Cheque must be a valid image or pdf.',
            'cheque_file_path.mimetypes' => 'Payment document image or PDFs are allowed.',
            'cheque_file_path.max' => 'Payment document file must not be greater than 10MB.',
            'cheque_file_path.*.file' => 'The uploaded Cheque must be a valid image or pdf.',
            'cheque_file_path.*.mimetypes' => 'Payment document image or PDFs are allowed.',
            'cheque_file_path.*.max' => 'Payment document file must not be greater than 10MB.',
        ];

        $validator = Validator::make($request->all(), $validationRules,  $validatinMsg );

        if ($validator->fails()) {
            foreach ($validator->messages()->getMessages() as $messages) {
                return redirect()->back()->with($this->alertError(__($messages[0])))->withInput();
            }
        }

        if(CommonClass::user()->role_id == 109){
            $subAgent = SubAgent::where('id', CommonClass::user()->profile_id)->first();
            $agent_id = $subAgent->agent_id;
            $sub_agent_id = $subAgent->id;
        }else{
            $agent_id = strtoupper( CommonClass::user()->profile_id);
            $sub_agent_id = 0;
        }

        $invoice_no = $request->get('invoice_no');
        $title = 'New Payment request';
        $type = 'CR';
        $payment_date = $request->get('payment_date');
        $amount = $request->get('amount');
        $bank_name = $request->get('bank_name');
        $acc_no = $request->get('acc_no');
        $account_name = $request->get('account_name');
        $trx_no = $request->get('trx_no');
        $currency_id = $request->get('currency_id');
        $status = 'P';
        $payment_method = $request->get('payment_method');
        $payment_receiver = $request->get('payment_receiver');
        $payment_received_location = $request->get('payment_received_location');
        $payment_time = $request->get('payment_time');
        $description = $request->get('description');


        $cheque_file_path = '';
        if ($request->hasFile('cheque_file_path')) {
            $slug = 'payments';
            $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
            $row = $dataType ? $dataType->addRows->where('field', 'cheque_file_path')->first() : null;
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
            if($error == 0 && $amount > 0){

                //Payment
                $entityPayment = new Payment();
                $entityPayment->title = $title. '. '.$transactionTitle;
                $entityPayment->type = $type;
                $entityPayment->agent_id = !empty($agent_id)?$agent_id:0;
                $entityPayment->sub_agent_id = !empty($sub_agent_id)?$sub_agent_id:0;
                $entityPayment->candidate_id = !empty($candidate_id)?$candidate_id:0;
                $entityPayment->teacher_id = !empty($teacher_id)?$teacher_id:0;
                $entityPayment->amount = $amount;
                $entityPayment->currency_id = $currency_id;
                $entityPayment->payment_date = $payment_date;
                $entityPayment->invoice_no = $invoice_no;
                $entityPayment->account_name = $account_name;
                $entityPayment->bank_name = $bank_name;
                $entityPayment->acc_no = $acc_no;
                $entityPayment->trx_no = $trx_no;
                $entityPayment->user_id = CommonClass::user()->id;
                $entityPayment->status = $status;
                $entityPayment->cheque_file_path = $cheque_file_path;
                $entityPayment->payment_method = $payment_method;
                $entityPayment->payment_receiver = $payment_receiver;
                $entityPayment->payment_received_location = $payment_received_location;
                $entityPayment->payment_time = $payment_time;
                $entityPayment->description = $description;

                if (!$entityPayment->save()) {
                    DB::rollBack();
                    $error++;
                    $errorMsg[] = 'Failed to add payment request';
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

            // Send notification to sub agent, Agent, and owner
            try {
                $amountFormatted = CommonClass::currencySymbol(true, $entityPayment->currency_id, $entityPayment->amount);
                $title = 'New Wallet Payment Request';
                $body = 'A new payment request of ' . $amountFormatted . ' (Invoice: ' . $entityPayment->invoice_no . ') has been submitted.';

                // Send to Owner
                CommonClass::sendTargetedNotificationAndEmail('owner', null, $title, $body);

                // Send to Agent
                if (!empty($entityPayment->agent_id)) {
                    CommonClass::sendTargetedNotificationAndEmail('agent', $entityPayment->agent_id, $title, $body);
                }

                // Send to Sub Agent
                if (!empty($entityPayment->sub_agent_id)) {
                    CommonClass::sendTargetedNotificationAndEmail('sub_agent', $entityPayment->sub_agent_id, $title, $body);
                }
            } catch (\Exception $e) {
                Log::error('Wallet request notification error: ' . $e->getMessage());
            }

            return redirect()->back()->with($this->alertSuccess(__('Payment request added successfully.')));
        } else {
            return redirect()->back()->with($this->alertError(__($errorMsg[0])))->withInput();
        }
    }

    public function candidate(Request $request)
    {

        $user = Auth::user();

        if($user){
            if($user->role->id == 101){
                $candidates = Candidate::with(['classGroup','agent', 'subAgent', 'agencier', 'companier'])->where('agent_id',CommonClass::user()->profile_id)->orderBy('created_at', 'desc')->get();

            }elseif($user->role->id == 108){
                $candidates = Candidate::with(['classGroup','agent', 'subAgent', 'agencier', 'companier'])->where('agencier_id',CommonClass::user()->profile_id)->orderBy('created_at', 'desc')->get();
            }elseif($user->role->id == 109){
                $candidates = Candidate::with(['classGroup','agent', 'subAgent', 'agencier', 'companier'])->where('sub_agent_id',CommonClass::user()->profile_id)->orderBy('created_at', 'desc')->get();
            }else{
                $candidates = Candidate::with(['classGroup', 'subAgent'])->orderBy('created_at', 'desc')->get();
            }
        }else{
            $candidates = Candidate::with(['classGroup', 'subAgent'])->orderBy('created_at', 'desc')->get();
        }
        return Voyager::view('voyager::my.candidate', ['candidates'=>$candidates]);
    }

    public function candidateCreate(Request $request)
    {
        $slug = 'candidates';
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
//        $this->authorize('add', app($dataType->model_name));

        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? new $dataType->model_name()
            : false;

        foreach ($dataType->addRows as $key => $row) {
            $dataType->addRows[$key]['col_width'] = $row->details->width ?? 100;
        }

        $this->removeRelationshipField($dataType, 'add');
        $isModelTranslatable = is_bread_translatable($dataTypeContent);
        $this->eagerLoadRelations($dataTypeContent, $dataType, 'add', $isModelTranslatable);
        $view = "voyager::my.edit-add-candidate";
        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }

    public function candidateStore(Request $request)
    {
        $request->merge(['email' => strtolower($request->get('email'))]);
        $request->merge(['code' => CommonClass::generateCode('C', $request->get('agent_id'))]);
        $validationRules = [
            'name' => 'required',
            'email' => [
                'required',
                'email',
                'unique:users,email',
                'unique:candidates,email'
            ],
            'code' => 'required|unique:candidates,code',
            'mobile' => 'required|unique:candidates,mobile',
            'passport_no' => 'required|unique:candidates,passport_no',
        ];
        $validatinMsg = [
            "email.required"=>"Candidate email is required",
            "email.email"=>"Candidate email is should be valid",
            "email.unique"=>"Candidate email is already exist",
        ];
        $validator = Validator::make($request->all(), $validationRules,  $validatinMsg );
        if ($validator->fails()) {
            foreach ($validator->messages()->getMessages() as $messages) {
                return redirect()->back()->with($this->alertError(__($messages[0])))->withInput();
            }
        }

        $role_id = 102;
        $slug = 'candidates';

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
//        $this->authorize('add', app($dataType->model_name));

        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows)->validate();
        $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());

        event(new BreadDataAdded($dataType, $data));

        //create user
        $password = !empty($request->password)?$request->password:'password';
        $user = User::create([
            'status'           => $data->status,
            'name'           => $request->name,
            'email'          => strtolower($request->email),
            'password'       => bcrypt($password),
            'remember_token' => Str::random(60),
            'role_id'        => $role_id,
            'user_type_id'        => $role_id,
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
                $redirect = redirect()->route("admin.my.candidate.index");
            } else {
                $redirect = redirect()->back();
            }

            $redirect = redirect()->route("admin.my.candidate.index");
            return $redirect->with([
                'message'    => __('voyager::generic.successfully_added_new')." {$dataType->getTranslatedAttribute('display_name_singular')}". 'And  Code is '.$data->code,
                'alert-type' => 'success',
            ]);
        } else {
            return response()->json(['success' => true, 'data' => $data]);
        }
    }

    public function candidateEdit(Request $request, $id)
    {
        $slug = 'candidates';
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
        $this->removeRelationshipField($dataType, 'edit');
        $isModelTranslatable = is_bread_translatable($dataTypeContent);
        $this->eagerLoadRelations($dataTypeContent, $dataType, 'edit', $isModelTranslatable);
        $view = "voyager::my.edit-add-candidate";
        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }

    public function candidateUpdate(Request $request, $id)
    {
        $slug = 'candidates';
        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Compatibility with Model binding.
        $id = $id instanceof \Illuminate\Database\Eloquent\Model ? $id->{$id->getKeyName()} : $id;

        $model = app($dataType->model_name);
        $query = $model->query();
        if ($dataType->scope && $dataType->scope != '' && method_exists($model, 'scope'.ucfirst($dataType->scope))) {
            $query = $query->{$dataType->scope}();
        }
        if ($model && in_array(SoftDeletes::class, class_uses_recursive($model))) {
            $query = $query->withTrashed();
        }

        $data = $query->findOrFail($id);

        $request->merge(['email' => strtolower($request->get('email'))]);
        $validationRules = [
            'name' => 'required',
            'email' => [
                'required',
                'email',
                'unique:users,email,' . User::where('candidate_id', $id)->pluck('id')->first(),
                'unique:candidates,email,' . $id
            ],
            'mobile' => 'required|unique:candidates,mobile,' . $id,
            'passport_no' => 'required|unique:candidates,passport_no,' . $id,
        ];
        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            foreach ($validator->messages()->getMessages() as $messages) {
                return redirect()->back()->with($this->alertError(__($messages[0])))->withInput();
            }
        }

        $val = $this->validateBread($request->all(), $dataType->editRows, $dataType->name, $id)->validate();
        $to_remove = $dataType->editRows->where('type', 'image')
            ->filter(function ($item, $key) use ($request) {
                return $request->hasFile($item->field);
            });
        $original_group_id = $data->class_group_id;
        $original_data = clone($data);

        $this->insertUpdateData($request, $slug, $dataType->editRows, $data);
        $this->deleteBreadImages($original_data, $to_remove);
        event(new BreadDataUpdated($dataType, $data));

        //update user
        if(!empty($request->password)){
            $userEntity = User::where('candidate_id', $data->id)->first();
            $userEntity->password = bcrypt($request->password);
            $userEntity->status = $data->status;
            $userEntity->update();
        }

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

//        $redirect = redirect()->back();
        $redirect = redirect()->route("admin.my.candidate.index");

        return $redirect->with([
            'message'    => __('voyager::generic.successfully_updated')." {$dataType->getTranslatedAttribute('display_name_singular')}",
            'alert-type' => 'success',
        ]);
    }

    public function paymentRequest(Request $request)
    {

        if(CommonClass::user()->role_id == 109){
            $paymentRequests = SubAgentPaymentRequest::with(['candidate'])
                ->where('sub_agent_id',CommonClass::user()->profile_id)->orderBy('created_at', 'desc')->get();
        }else{
            $paymentRequests = PaymentRequest::with(['candidate'])
                ->where('agent_id',CommonClass::user()->profile_id)->orderBy('created_at', 'desc')->get();
        }

        return Voyager::view('voyager::my.payment-request', ['paymentRequests'=>$paymentRequests]);
    }

}
