<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonClass;
use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Events\BreadDataUpdated;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as VoyagerBaseController;
use TCG\Voyager\Models\User;

class VoyagerTeacherController extends VoyagerBaseController
{
    private $role_id = 103;


    public function store(Request $request)
    {
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        $request->merge(['code' => CommonClass::generateCode('T')]);
        $request->merge(['email' => strtolower($request->get('email'))]);
        $validator = Validator::make($request->all(), [
            'code' => 'required|unique:teachers,code',
            'mobile' => 'required|unique:teachers,mobile',
            'email' => 'required|email|unique:users,email',
        ]);
        if ($validator->fails()) {
            foreach ($validator->messages()->getMessages() as $messages) {
                return redirect()->back()->with($this->alertError(__($messages[0])))->withInput();
            }
        }
        // Validate fields with ajax
        $val = $this->validateBread($request->all(), $dataType->addRows)->validate();
        $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());

        event(new BreadDataAdded($dataType, $data));

        //create user
        $password = !empty($request->password)?$request->password:'password';
        $user = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => bcrypt($password),
            'remember_token' => Str::random(60),
            'role_id'        => $this->role_id,
            'user_type_id'        => $this->role_id,
            'teacher_id'        => $data->id,
            'avatar'        => 'users/default.png'
        ]);
        if($user && !empty($password) && !empty($request->email)){
            CommonClass::sendEmail('Teacher Registration', $request->email, ['username'=>$request->email, 'password'=>$password], 'email.registration');
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
        $original_data = clone($data);

        $this->insertUpdateData($request, $slug, $dataType->editRows, $data);

        // Delete Images
        $this->deleteBreadImages($original_data, $to_remove);

        event(new BreadDataUpdated($dataType, $data));

        //update user
        if(!empty($request->password)){
            $userEntity = User::where('teacher_id', $data->id)->first();
            $userEntity->password = bcrypt($request->password);
            $user = $userEntity->update();

            if($user){
                CommonClass::sendEmail('Update Teacher Information', $request->email, ['username'=>$request->email, 'password'=>$request->password], 'email.registration');
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


}
