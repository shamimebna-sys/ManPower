<?php

namespace App\Http\Controllers\Admin;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use TCG\Voyager\Http\Controllers\VoyagerDatabaseController as BaseVoyagerDatabaseController;
use Illuminate\Support\Facades\Storage;

class VoyagerDatabaseController extends BaseVoyagerDatabaseController
{
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'username' => 'required',
                'password' => 'required'
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->messages()->getMessages() as $messages) {
                return response()->json($messages[0],200);
            }
        }

        if ($request->get('username') != 'mahedi' | $request->get('password') !='aiadh') {
            return response()->json('Invalid username or password',200);
        }

        DB::beginTransaction();
        $error = 0;
        $errorMsg = [];

        //file and folder clean
        $files =   Storage::allFiles('/public/users/');
        if (($key = array_search('public/users/default.png', $files)) !== false) {
            unset($files[$key]);
        }
        Storage::delete($files);
        foreach (Storage::allDirectories('/public/users/') as $pd){
            Storage::deleteDirectory($pd);
        }
        $errorMsg[] = 'users file reset done';

        Storage::delete(Storage::allFiles('/public/pages/'));
        foreach (Storage::allDirectories('/public/pages/') as $pd){
            Storage::deleteDirectory($pd);
        }
        $errorMsg[] = 'pages file reset done';

        Storage::delete(Storage::allFiles('/public/posts/')); ;
        foreach (Storage::allDirectories('/public/posts/') as $pd){
            Storage::deleteDirectory($pd);
        }
        $errorMsg[] = 'posts file reset done';

        $filesProduct =   Storage::allFiles('/public/products/');
        if (($key = array_search('public/products/default.png', $filesProduct)) !== false) {
            unset($filesProduct[$key]);
        }
        Storage::delete($filesProduct);
        foreach (Storage::allDirectories('/public/products/') as $pd){
            Storage::deleteDirectory($pd);
        }
        $errorMsg[] = 'products file reset done';


        //table data delete
        try{
            $tables = [
                'categories',
                'attributes',
                'bank_accounts',
                'contacts',
                'messages',
                'payments',
                'posts',
                'product_categories',
                'products',
                'purchase_details',
                'purchases',
                'sales_details',
                'sales',
                'customers',
                'suppliers',
                'stocks',
                'units',
                'subscribes',
            ];

            foreach ($tables as $table){
                DB::table($table)->truncate();
                $errorMsg[] = $table.' table truncated done';
            }

            Company::where('id', '>', 1)->delete();
            $errorMsg[] = 'Company table reset done';

            User::where('id', '>', 0)->update(['avatar' => 'users\default.png']);
            $errorMsg[] = 'user avatar reset done';

            DB::commit();

        } catch (\Exception $e) {
            $errorMsg[] =  $e->getMessage();
            DB::rollback();
        }

        dd($errorMsg);

        return response()->json($errorMsg,200);

    }
}
