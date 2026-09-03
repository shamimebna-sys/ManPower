<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonClass;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerUserController as BaseVoyagerUserController;

class VoyagerUserController extends BaseVoyagerUserController
{
    public function relation(Request $request)
    {
        if (in_array($request->type, ['user_belongsto_role_relationship', 'user_belongstomany_role_relationship'])) {
            $query = (CommonClass::user()->role_id !=1)?Role::where('id','>', 1):Role::where('id','>', 0);
            if ($request->has('search')) {
                $query->where('display_name', 'LIKE', '%' . $request->search . '%');
            }

            $roles = [
                'pagination'=>['more'=>false],
                'results'=>[
                    ['id'=>'','text'=>'None' ]
                ]
            ];
            foreach ($query->get() as $d){
                $roles['results'][] = [
                    'id'=>$d->id,
                    'text'=>$d->display_name
                ];
            }
            return response()->json($roles);
        }

        return parent::relation($request);
    }

    public function profile(Request $request)
    {
        $route = '';
        $dataType = Voyager::model('DataType')->where('model_name', Auth::guard(app('VoyagerGuard'))->getProvider()->getModel())->first();
        if (!$dataType && app('VoyagerGuard') == 'web') {
            $route = route('voyager.users.edit', Auth::user()->getKey());
        } elseif ($dataType) {
            $route = route('voyager.'.$dataType->slug.'.edit', Auth::user()->getKey());
        }

        $user = \Auth::user();
        if($user->role->id == 101){
            $id = \Auth::user()->agent_id;
            $slug = 'agents';
        }elseif($user->role->id == 102){
            $id = \Auth::user()->candidate_id;
            $slug = 'candidates';
            return redirect()->route('admin.my.profile.index');
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

        return Voyager::view('voyager::profile', compact('route'));
    }


}
