<?php

namespace App\Managers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use phpDocumentor\Reflection\DocBlock\Tags\Uses;

class AuthManager
{
    public function register($data = [])
    {
        $error = 0;
        try {
            DB::beginTransaction();
            if (Company::where('sub_domain', $data['sub_domain'])->count() > 0) {
                $error++;
                $response = [
                    'status' => false,
                    'code' => 1,
                    'msg' => 'Domain name is not available.'
                ];
            } else {
                $companyEntity = new Company();
                $companyEntity->name = $data['business_name'];
                $companyEntity->email = $data['email'];
                $companyEntity->mobile = $data['mobile'];
                $companyEntity->status = 'A';
                $companyEntity->sub_domain = $data['sub_domain'];
                $companyEntity->package_id = (isset($data['package_id']) && !empty($data['package_id'])) ? $data['package_id']:1;
                $companyEntity->expire_at = date('Y-m-d', strtotime(date('Y-m-d') . ' +10 day'));
                $companyEntity->is_paid =(isset($data['is_paid']) && !empty($data['is_paid'])) ? $data['is_paid']:'N';
                $companyEntity->address = (isset($data['business_address']) && !empty($data['business_address']))?$data['business_address']:'';
                $companyEntity->custom_domain = (isset($data['custom_domain']) && !empty($data['custom_domain'])) ? $data['custom_domain']:'';

                if (!$companyEntity->save()) {
                    $error++;
                    $response = [
                        'status' => false,
                        'code' => 2,
                        'msg' => 'Domain name is not available.'
                    ];
                } else {

                    $password = rand(9999, date('Ymd'));
                    $userEntity = new User();
                    $userEntity->name = (isset($data['name']) && !empty($data['name'])) ? $data['name']:'User';
                    $userEntity->email = $data['email'];
                    $userEntity->mobile = $data['mobile'];
                    $userEntity->password = (isset($data['password']) && !empty($data['password'])) ? bcrypt($data['password']):bcrypt($password);
                    $userEntity->role_id = 3;
                    $userEntity->company_id = $companyEntity->id;
                    if (!$userEntity->save()) {
                        $error++;
                        $response = [
                            'status' => false,
                            'code' => 3,
                            'msg' => 'Domain name is not available.'
                        ];
                    } else {
                        $response = [
                            'status' => true,
                            'code' => 0,
                            'msg' => 'Registration successfully done. A email has been sent to you email. Check your email for further access.',
                            'token' => $userEntity->createToken('tokens')->plainTextToken
                        ];

                        try {
                            Mail::send('email.registration', ['password' => $password], function ($message) use ($userEntity) {
                                $message->from( env('MAIL_FROM_ADDRESS'),  env('MAIL_FROM_NAME') );
                                $message->to($userEntity->email);
                                $message->subject(env('APP_NAME') . ' Registration');
                            });
                        }catch (\Exception $e){}
                    }
                }

            }

        } catch (\Exception $e) {
            $error++;
            $response = [
                'status' => false,
                'code' => 999,
                'msg' => 'Something went wrong. Please contact with site developer.'
            ];
            Log::error(__FUNCTION__ . " in " . __FILE__ . " at " . __LINE__ . ': ' . $e->getMessage());
        }

        if ($error == 0) {
            DB::commit();
        } else {
            DB::rollback();
        }
        return $response;
    }

    public function companyUpdate($id, $data = [])
    {
        $error = 0;
        try {
            DB::beginTransaction();
            $companyEntity = Company::findOrFail($id);
            if ($companyEntity) {
                $companyEntity->name = $data['business_name'];
                $companyEntity->email = $data['email'];
                $companyEntity->mobile = $data['mobile'];
                $companyEntity->address = $data['business_address'];

                if ($companyEntity->update()) {
                    $response = [
                        'status' => true,
                        'code' => 0,
                        'msg' => 'Company profile updated'
                    ];
                } else {
                    $error++;
                    $response = [
                        'status' => false,
                        'code' => 1,
                        'msg' => 'Something went wrong.'
                    ];
                }
            } else {
                $error++;
                $response = [
                    'status' => false,
                    'code' => 2,
                    'msg' => 'Invalid company information.'
                ];
            }
        } catch (\Exception $e) {
            $error++;
            $response = [
                'status' => false,
                'code' => 999,
                'msg' => 'Something went wrong. Please contact with site developer.'
            ];
            Log::error(__FUNCTION__ . " in " . __FILE__ . " at " . __LINE__ . ': ' . $e->getMessage());
        }

        if ($error == 0) {
            DB::commit();
        } else {
            DB::rollback();
        }
        return $response;
    }

    public function userUpdate($id, $data)
    {
        $error = 0;
        try {
            DB::beginTransaction();
            $userEntity = User::findOrFail($id);
            if($userEntity){
                $userEntity->name = $data['name'];
                if (isset($data['status']) && !empty($data['status'])) {
                    $userEntity->status = $data['status'];
                }
                if (isset($data['role_id']) && !empty($data['role_id'])) {
                    $userEntity->role_id = $data['role_id'];
                }
                if (isset($data['password']) && !empty($data['password'])) {
                    $userEntity->password = bcrypt($data['password']);
                }

                if ($userEntity->update()) {
                    $response = [
                        'status' => true,
                        'code' => 0,
                        'msg' => 'user profile updated'
                    ];
                } else {
                    $error++;
                    $response = [
                        'status' => false,
                        'code' => 1,
                        'msg' => 'Failed to update user profile'
                    ];
                }
            }else{
                $error++;
                $response = [
                    'status' => false,
                    'code' => 2,
                    'msg' => 'User profile not find'
                ];
            }
        } catch (\Exception $e) {
            $error++;
            $response = [
                'status' => false,
                'code' => 999,
                'msg' => 'Something went wrong. Please contact with site developer.'
            ];
            Log::error(__FUNCTION__ . " in " . __FILE__ . " at " . __LINE__ . ': ' . $e->getMessage());
        }

        if ($error == 0) {
            DB::commit();
        } else {
            DB::rollback();
        }
        return $response;
    }

    public function login($data){
        $credentials = ['email' => $data['email'], 'password' => $data['password']];
        if (auth()->attempt($credentials)) {
            $response = [
                'status' => true,
                'code' => 0,
                'msg' => 'Login successful',
                'data' => auth()->user(),
                'token' => auth()->user()->createToken('API Token')->plainTextToken
            ];
        }else{
            $response = [
                'status' => false,
                'code' => 1,
                'msg' => 'Wrong email or password'
            ];
        }
        return $response;
    }

}
