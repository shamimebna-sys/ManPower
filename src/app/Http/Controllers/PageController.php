<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttributeList;
use App\Models\Candidate;
use App\Models\ClassGroup;
use App\Models\CurricularList;
use App\Models\Education;
use App\Models\ExpertiseList;
use App\Models\Exprience;
use App\Models\InterestList;
use App\Models\LanguageList;
use App\Models\Skill;
use App\Models\SkillList;
use App\Models\Training;
use App\Models\User;
use Illuminate\Http\Request;
use TCG\Voyager\Models\Post;

class PageController extends Controller
{

    public function home(Request $request)
    {
        $blog = Post::with(['authorId'])->limit(3)->orderBy('created_at', 'desc')->get();
        return view('home',['blog'=>$blog]);
    }

    public function about(Request $request)
    {
        return view('about');
    }

    public function faq(Request $request)
    {
        return view('faq');
    }

    public function contact(Request $request)
    {
        return view('contact');
    }
	
	 public function down(Request $request)
    {
        return view('down');
    }


    public function candidates(Request $request, $code=null)
    {
        $groups = ClassGroup::where('name', 'like', '%Rapid%')
            ->where('status', 'A')
            ->get()
            ->map(function ($group) {
                $group->name = ucwords(str_replace('rapid interview for ', '', strtolower($group->name)));
                return $group;
            });

        $data = $dataCandidate = Candidate::with(['classGroup', 'latestExamResult'])->where('status', 'A')
            ->whereHas('classGroup', function ($query) {
                $query->where('name', 'like', '%Rapid%');
            });

        
        if(!empty($code)) {
             $dataCandidate  =  $dataCandidate->where('code', $code)->first();
	
            if(  $dataCandidate) {

                $user = User::where('candidate_id',  $dataCandidate->id)->first();
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

                return view('candidate-details', [
                    'data' =>  $dataCandidate,
                    'expriences' => $expriences,
                    'trainings' => $trainings,
                    'skills' => $skills,
                    'educations' => $educations,
                    'languageList' => $languageList,
                    'expertiseList' => $expertiseList,
                    'skillList' => $skillList,
                    'curricularList' => $curricularList,
                    'interestList' => $interestList,
                    'attributeList' => $attributeList,
                ]);
            }
        } 

            $purpose  = $request->get('purpose');
            if(!empty($purpose) && \Auth::user()){
                $purpose = strtoupper($purpose);
                $employer_id  = \App\Helpers\CommonClass::user()->employer_id;
                $data= $data->whereHas('employerCandidates', function ($query) use($purpose, $employer_id) {
                    $query->where('employer_id',$employer_id );
                    $query->where('purpose', $purpose);
                    $query->where('status', 'A');
                });
            }

            $search  = $request->get('search');
            if(!empty($search)){
                $data = $data->where('name','like', '%'.$search.'%');
            }

            $group  = $request->get('group');
            if (!empty($group)) {
                $data = $data->where('class_group_id', $group);
            }

            $gender  = $request->get('gender');
            if (!empty($gender)) {
                $data = $data->where('gender', $gender);
            }

//dd($data->toSql());

            $data = $data->orderBy('id', 'desc')->paginate(20)->withQueryString();
            return view('candidates', ['data'=>$data, 'groups'=>$groups]);
		 
        
    }
    public function employers(Request $request, $id=null)
    {
        if(!empty($id)){

        }else{

            return view('employers');
        }
    }

}
