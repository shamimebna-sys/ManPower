<?php

namespace App\Helpers;

use App\Models\Agent;
use App\Models\Arc;
use App\Models\Candidate;
use App\Models\Currency;
use App\Models\EmployerCandidate;
use App\Models\FlightSchedule;
use App\Models\LabourContract;
use App\Models\LiveStatus;
use App\Models\ManpowerTraining;
use App\Models\Notification;
use App\Models\PaymentRequest;
use App\Models\PoliceClearance;
use App\Models\Stock;
use App\Models\Teacher;
use App\Models\VisaImmigration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Facades\Image;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Picqer;
use TCG\Voyager\Models\User;

class CommonClass
{
    public static function dateTimeFormat($datetime, $type='datetime')
    {
        if($type == 'datetime'){
            return date("Y-m-d h:i A", strtotime($datetime));

        }elseif($type == 'date'){
            return date("Y-m-d", strtotime($datetime));

        }elseif($type == 'time'){
            return date("h:i A", strtotime($datetime));

        }else{
            return 'Invalid format';
        }
    }

    public static function jsonValidation($data=NULL){

        if (!empty($data)) {
            @json_decode($data);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }

    public static function uploadImage($fileObj, $folder=''){
        try{
            //make thumbnail function
            $createThumbnail = function($sourcePath,$thumbnailPath, $width, $height, $resizeWithRatio = true){

                if($resizeWithRatio){ //ration based
                    $img = Image::make($sourcePath)->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $img->save($thumbnailPath);

                }else{ //fixed size based
                    $img = Image::make($sourcePath)->resize($width, $height);
                    $img->save($thumbnailPath);
                }
            };

            //image info
            $filenamewithextension = $fileObj->getClientOriginalName();
            $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);
            $extension = $fileObj->getClientOriginalExtension();

            //filename to store
//            $baseImgName = $filename.'_'.time().'.'.$extension;
            $baseImgName = date('Ymdhis').'_'.rand(111111,999999999).'.'.$extension;
            $originalImg = 'original_'.$baseImgName;
            $smallImg = 'small_'.$baseImgName;
            $mediumImg = 'medium_'.$baseImgName;
            $largeImg = 'large_'.$baseImgName;

            //Upload File
            $fileObj->move(public_path('upload/'.$folder.'/'), $originalImg);
            $createThumbnail('upload/'.$folder.'/'.$originalImg, 'upload/'.$folder.'/'.$smallImg,150, 93);
            $createThumbnail('upload/'.$folder.'/'.$originalImg, 'upload/'.$folder.'/'.$mediumImg,300, 185);
            $createThumbnail('upload/'.$folder.'/'.$originalImg, 'upload/'.$folder.'/'.$largeImg,550, 340);

            $file = [
                'base_name' => $baseImgName,
                'original' => 'upload/'.$folder.'/'.$originalImg,
                'large' => 'upload/'.$folder.'/'.$largeImg,
                'medium' => 'upload/'.$folder.'/'.$mediumImg,
                'small' => 'upload/'.$folder.'/'.$smallImg,
            ];

        }catch (\Exception $e){
            return $file = false;
        }
        return $file;
    }

    public static function getImagePath($folder='', $size='small', $baseName='', $returnPath = false){
        return  'https://via.placeholder.com/100?text=Preview';

        if($returnPath){
            return asset('upload/'.$folder.'/'.$size.'_');
        }else{
            $path = 'upload/'.$folder.'/'.$size.'_'.$baseName;
            if($baseName && file_exists($path)){
                return  asset($path);
            }else{
                return asset('img/preview.png');
//           return  'https://via.placeholder.com/100?text=Preview';
            }
        }

    }

    public static function uploadFile($fileObj, $folder=''){
        try{
            //image info
            $filenamewithextension = $fileObj->getClientOriginalName();
            $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);
            $extension = $fileObj->getClientOriginalExtension();

            //filename to store
            $originalImg = $filename.'_'.time().'.'.$extension;

            //Upload File
            $fileObj->move(public_path('upload/'.$folder.'/'), $originalImg);

            $file = [
                'name' => $originalImg,
                'path' => 'upload/'.$folder.'/'.$originalImg
            ];

        }catch (\Exception $e){
            return $file = false;
        }
        return $file;
    }

    public static function invoiceNo($prefix='T'){
        return self::user()->company_id.date('ymdh').self::user()->id.rand(10, 999);
        return date('my').strtoupper(uniqid());
        return date('my').strtoupper(uniqid()).rand(11, 999);
        return $prefix.'-'.date('Ymdhis').rand(100, 9999);
    }

    public static function getProductAttributeMatrix($input =[]) {
        /*$input=[
           'size'=>[
               ['id'=>11, 'name'=>'XL'],
               ['id'=>23, 'name'=>'M'],
           ],
           'unit'=>[
               ['id'=>1, 'name'=>'kg'],
               ['id'=>2, 'name'=>'g'],
           ],
           'brand'=>[
               ['id'=>1, 'name'=>'metro'],
               ['id'=>2, 'name'=>'mahedi'],
               ['id'=>'', 'name'=>'test'],
           ],
       ];*/

        $result = [[]];
        foreach ($input as $key => $values) {
            $append = [];
            foreach ($values as $id=>$name) {
                foreach ($result as $data) {
                    $append[] = $data + [$key => $name];
                }
            }
            $result = $append;
        }
        return $result;
    }

    public static function getStockByProductAttribute($params, $variant_display){
        $product_id = isset($params['product_id'])?$params['product_id']:null;
        return Stock::where('product_id',$product_id)->where('variant_display',$variant_display)->first();
    }

    public static function generateBarcode($prefix = ''){
//        $unique = strtoupper(uniqid($prefix));
        $ran =  str_pad(strtoupper(mt_rand( 100, date('Y'))),4,"0",STR_PAD_LEFT);
        $stockMax =Stock::max('id')+1;
        $unique = $prefix.str_pad($stockMax.$ran,12,"0",STR_PAD_LEFT);
        $check = Stock::where('sku', $unique)->first();
        if ($check) {
            return self::generateBarcode($prefix);
        }
        return $unique;
    }

    public static function getBarcodeImgUri($code){
        $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
        $barcode = base64_encode($generator->getBarcode($code, $generator::TYPE_CODE_128, 1,50));
        return "data:image/png;base64,".$barcode;
//        echo  "<img  src='data:image/png;base64,".$barcode."' /><br>".$code.'<br>';
    }

    public static function getQRCodeImg($content=''){
        return QrCode::size(100)->generate($content);
    }

    public static function user(){
        $user = Auth::user();
        if($user){
            if($user->role->id == 101){
                $user->profile_id = $user->agent_id;
                $user->profile_slug = 'agents';
            }elseif($user->role->id == 102){
                $user->profile_id = $user->candidate_id;
                $user->profile_slug  = 'candidates';
            }elseif($user->role->id == 103){
                $user->profile_id = $user->teacher_id;
                $user->profile_slug  = 'teachers';
            }elseif($user->role->id == 104){
                $user->profile_id = $user->employee_id;
                $user->profile_slug  = 'employees';
            }elseif($user->role->id == 105){
                $user->profile_id = $user->employer_id;
                $user->profile_slug  = 'employers';
            }else{
                $user->profile_id = null;
                $user->profile_slug  = 'users';
            }
        }
        return $user;
    }

    public static function currencySymbol($symbol=true, $id=null, $amount=null){

        if(!empty($id)){
            $currency = Currency::find($id);
            if($currency){
                if(!empty($amount)){
                    if ($currency->position == 'A'){
                        return number_format($amount, 2).' '. (($symbol)? $currency->symbol:$currency->short_name);
                    }else{
                        return (($symbol)? $currency->symbol:$currency->short_name). ' '.number_format($amount, 2);
                    }
                }else{
                    return ($symbol)? $currency->symbol:$currency->short_name;
                }
            }else{
                return '';
            }
        }else{
            return ($symbol)? '৳':'BDT';
        }

    }

    public static function sendEmail(  $subject='', $toEmail='', $templateParams=[],$template='email.default'){
        try {
            Mail::send($template, $templateParams, function ($message) use ($toEmail, $subject) {
                $message->from( env('MAIL_FROM_ADDRESS'),  env('MAIL_FROM_NAME') );
                $message->to($toEmail);
                $message->subject(env('APP_NAME').' '. $subject);
            });
        }catch (\Exception $e){
            \Log::error($e->getMessage());
        }

    }

    public static function getPaymentStatus($value = ''){
        $option = '';
        switch ($value){
            case 'P':  $option = '<b class="text-warning">Pending</b>'; break;
            case 'A':  $option = '<b class="text-success">Approved</b>'; break;
            case 'R':  $option = '<b class="text-danger">Rejected</b>'; break;
            case 'I':  $option = '<b class="text-warning">Inactive</b>'; break;
            default: $option = '<b class="text-warning">Unknown</b>'; break;
        }
        return $option;
    }

    public static function generateCode($type = 'C', $id = null, $len = 3){
        $code = '';
        if($type == 'A'){
            $max = Agent::max('id')+1;
            $code = '1'.str_pad($max, 3, '0', STR_PAD_LEFT);
        }elseif($type == 'T'){
            $agent = Teacher::max('id')+1;
            $code = '2'.str_pad($agent, 3, '0', STR_PAD_LEFT);
        }elseif($type == 'C'){
            $id =
            $agent = Agent::find($id);
            $max = Candidate::max('id')+1;
            $code = ($agent)?$agent->code:'9';
            $code = $code.str_pad($max, 4, '0', STR_PAD_LEFT);
        }else{
            $code = date('ymdhis').rand(100, 999);
        }
        return $code;

    }

    public static function sendNotificationAndEmail($messageCode=null, $groupId=null, $userId=null, $title=null, $message=null,  $redirectUrl=null, $sendEmail=false){

        try{
            if(!empty($message) && !empty($userId)){
                $user = User::find($userId);

                $notification = new Notification();
                $notification->user_id = $userId;
                $notification->title = $title;
                $notification->message = $message;
                $notification->group_id = $groupId;
                $notification->code = $messageCode;
                $notification->redirect_url= $redirectUrl;
                $notification->save();

                if($sendEmail){
                    self::sendEmail($title, 'mahedi.cnsbd@gmail.com', ['user'=>$user, 'notification'=>$notification], 'email.notification');
                }
            }
        }catch (\Exception $e){

        }


    }

    public static function mpVisaAndPaymentStatus($candidateId = null){
        /*
         * Done-green: Manpower Fee collected
         * Pending-yellow: Manpower Fee not collected & ticket updated and flight date not over
         * Pending-red: Manpower Fee not collected & flight date is over
         * Visa Updated-green: Visa file updated
        পেন্ডিং রেড কালার: যখন চাকরিপ্রার্থীর ফ্লাইটের ডেট পার হয়ে যাবে কিন্তু পেমেন্ট কমপ্লিট হবে না তখন স্ট্যাটাসটিক অটোমেটিক পেন্ডিং রেড কালার হয়ে যাবে।
        ডান: যখন তাকে প্রার্থীর ম্যান পাওয়ার ফি কালেকশন কমপ্লিট হবে তখন তার স্ট্যাটাসটি ডান সবুজ কালার হয়ে যাবে
        */


        if(!empty($candidateId)){
            $visa = VisaImmigration::where('candidate_id', $candidateId)->latest()->first();
            $flight = FlightSchedule::where('candidate_id', $candidateId)->latest()->first();
            if($flight){
                $flightDate = $flight->flight_date;
                $today = date('Y-m-d');
                $flightDateOver = (strtotime($flightDate) < strtotime($today));
            }


            $manpowerFeeCollection = PaymentRequest::where(function($query) {
                $query->where('bill_title', 'like', '%Manpower%')
                    ->orWhereRaw('bill_title = bill_title');
            })->where('status', 'A')->where('candidate_id', $candidateId)->latest()->first();

//            if($visa && $manpowerFeeCollection && $flight){
            if($manpowerFeeCollection ){
                return '<span class="btn btn-success btn-sm"  title="Manpower Fee collected">Done</span>';

            }elseif($visa && !$manpowerFeeCollection && $flight && !$flightDateOver){
                return '<span class="btn btn-warning btn-sm" title="Manpower Fee not collected & flight date not over">Pending</span>';

            }elseif($visa && !$manpowerFeeCollection && $flight && $flightDateOver){
                return '<span class="btn btn-danger btn-sm" title="Manpower Fee not collected & flight date is over">Pending</span>';

            }elseif($visa){
                return '<span class="btn btn-warning btn-sm" title="Visa file updated">VISA UPDATED</span>';

            }else{
                return '<span class="btn btn-default btn-sm" title="Visa file not updated">VISA NOT UPDATED</span>';
            }

        }else{
            return  '';
        }

    }

    public static function labourStatus($candidateId = null){
        $data = LabourContract::where('candidate_id', $candidateId)->latest()->first();
        return ($data);

    }
    public static function policeClearanceStatus($candidateId = null){
        $data = PoliceClearance::where('candidate_id', $candidateId)->latest()->first();
        return ($data);

    }
    public static function visaStatus($candidateId = null){
        $data = VisaImmigration::where('candidate_id', $candidateId)->latest()->first();
        return ($data);

    }
    public static function bmetStatus($candidateId = null){
        $data = ManpowerTraining::where('candidate_id', $candidateId)->latest()->first();
        return ($data);

    }
    public static function manpowerStatus($candidateId = null){
        $data = PaymentRequest::where(function($query) {
            $query->where('bill_title', 'like', '%Manpower%')
                ->orWhereRaw('bill_title = bill_title');
        })->where('status', 'A')->where('candidate_id', $candidateId)->latest()->first();
        return ($data);

    }
    public static function flightStatus($candidateId = null){
        $data = FlightSchedule::where('candidate_id', $candidateId)->latest()->first();
        return ($data);

    }
    public static function arcStatus($candidateId = null){
        $data = Arc::where('candidate_id', $candidateId)->latest()->first();
        return ($data);

    }

    public static function liveStatus($candidateId = null){
        $stepNo  = 1;
        $candidate = Candidate::find($candidateId);
        if($candidateId){

            if (\App\Helpers\CommonClass::flightStatus($candidate->id)) {
                $stepNo = 10;
            }
            elseif (\App\Helpers\CommonClass::manpowerStatus($candidate->id)) {
                $stepNo = 9;
            }
            elseif (\App\Helpers\CommonClass::visaStatus($candidate->id)) {
                $stepNo = 8;
            }
            elseif (\App\Helpers\CommonClass::policeClearanceStatus($candidate->id)) {
                $stepNo = 7;
            }
            elseif (\App\Helpers\CommonClass::labourStatus($candidate->id)) {
                $stepNo = 6;
            }
            elseif (isset($candidate->classGroup) && !empty($candidate->classGroup->name) && strpos($candidate->classGroup->name, 'Rapid') !== false) {
                $stepNo = 4;
            }
            elseif (isset($candidate->classGroup) && !empty($candidate->classGroup->name)) {
                $stepNo = 3;
            }elseif( count(json_decode($candidate->cv_file_path))>0){
                $stepNo = 2;
            }
        }

        $liveStatus = LiveStatus::where('step_no', $stepNo)->first();
        if($liveStatus){
            return $liveStatus->name;
        }else{
            return '';
        }


    }

    public static function checkEmployerCandidates($candidate_id= null, $employer_id = null, $purpose= null){
        $employerCandidate  = EmployerCandidate::where('purpose', $purpose)
            ->where('candidate_id', $candidate_id)
            ->where('employer_id', $employer_id)
            ->first();

        if($employerCandidate){
            return true;
        }else{
            return false;
        }


    }



}
