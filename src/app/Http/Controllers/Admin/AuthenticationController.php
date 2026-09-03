<?php

namespace App\Http\Controllers\Admin;

use App\Managers\AuthManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use TCG\Voyager\Events\BreadDataAdded;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as VoyagerBaseController;

class AuthenticationController extends VoyagerBaseController
{
    private $authManager;
    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    public function registration(Request $request){
        if($request->method() == 'POST'){
            $request->validate([
                //            'name' => 'required',
                'email' => 'required|email|unique:users,email,$this->id,id',
                //            'password' => 'required|string|min:6',
                'mobile' => 'required|numeric|digits:11|unique:users,mobile,$this->id,id',
                'business_name' => 'required',
                //            'business_address' => 'required',
                'sub_domain' => 'required|alpha|unique:companies,sub_domain,$this->id,id',
                //            'package_id' => 'required',
            ],
                [
                    'package_id.required'=>'The package field is required',
                    'sub_domain.unique'=>'The domain has already been taken. Try another in 6-10 char'
                ]);

            $data = $this->authManager->register( $request );
            if($data['status']){
                return redirect(route('voyager.registrationSuccess'));
            }

        }
        return view('vendor.voyager.registration');
    }

    public function registrationSuccess(){
        return view('vendor.voyager.registration-success');
    }

    public function registrationPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required|min:10|max:10',
            'address' => 'required',
            'cv' => 'required|max:2048',
            'ssc' => ' max:2048',
            'hsc' => 'max:2048',
            'bsc' => 'max:2048',
        ],
            [
                'cv.required'=>'CV file is required',
                'cv.max'=>'CV file is max 2MB ',
            ]
        );

        if ($validator->fails()) {
            foreach($validator->messages()->getMessages() as $messages) {
                return redirect()->back()->with(['status'=>false, 'message'=>$messages[0]])->withInput();
            }
        } else{
            try {
                $slug = 'career-applications';
                $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
                $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());

                event(new BreadDataAdded($dataType, $data));

                $msg = ['status' => true, 'message' => 'Application submitted successfully.'];
                return redirect(route('web.page', 'career'))->with($msg);

            } catch (\Exception $e) {
                $msg = ['status' => false, 'message' => 'Something went wrong. Please try again later.'];
                return redirect()->back()->with($msg)->withInput();
            }
        }
    }


    public function forgot(Request $request){
        if($request->method() == 'POST'){
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

//            $data = $this->authManager->register( $request );
            /*if($data['status']){
                return redirect(route('voyager.registrationSuccess'));
            }*/

        }
        return view('vendor.voyager.forgot');
    }


}
