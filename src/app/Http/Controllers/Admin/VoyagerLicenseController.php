<?php

namespace App\Http\Controllers\Admin;

use App\Models\Candidate;
use App\Models\Liense;
use App\Models\LicensePosition;
use Illuminate\Http\Request;
use PHPUnit\Exception;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as VoyagerBaseController;

class VoyagerLicenseController extends VoyagerBaseController
{
    // POST BRE(A)D
    public function store(Request $request)
    {
        //dd($request->all());

        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        //Validate fields
        $val = $this->validateBread($request->all(), $dataType->addRows)->validate();

        $data = new $dataType->model_name();
        $this->insertUpdateData($request, $slug, $dataType->addRows, $data);

        //line head data
        $license_position_id = $request->get('license_position_id');
        $license_position_quantity = $request->get('license_position_quantity');

        $licensePositionData = [];
        if(count($license_position_id)){
            LicensePosition::where('license_id', $data->id)->delete();
            foreach ( $license_position_id as $i=>$head){
                $licensePositionData[] = [
                    'license_id' =>$data->id,
                    'position_id' =>$license_position_id[$i],
                    'quantity' =>$license_position_quantity[$i],
                ];
            }
            if(count($licensePositionData)){
                LicensePosition::insert($licensePositionData);
            }
        }

//        $data->permissions()->sync($request->input('permissions', []));

        return redirect()
            ->route("voyager.{$dataType->slug}.index")
            ->with([
                'message'    => __('voyager::generic.successfully_added_new')." {$dataType->getTranslatedAttribute('display_name_singular')}",
                'alert-type' => 'success',
            ]);
    }

    // POST BR(E)AD
    public function update(Request $request, $id)
    {
        //dd($request->all());
        $slug = $this->getSlug($request);

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('edit', app($dataType->model_name));

        //Validate fields
        $val = $this->validateBread($request->all(), $dataType->editRows, $dataType->name, $id)->validate();

        $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);
        $this->insertUpdateData($request, $slug, $dataType->editRows, $data);

        //line head data
        //line head data
        $license_position_id = $request->get('license_position_id');
        $license_position_quantity = $request->get('license_position_quantity');
        $licensePositionData = [];
        if(count($license_position_id)){
            LicensePosition::where('license_id', $data->id)->delete();
            foreach ( $license_position_id as $i=>$head){
                $licensePositionData[] = [
                    'license_id' =>$data->id,
                    'position_id' =>$license_position_id[$i],
                    'quantity' =>$license_position_quantity[$i],
                ];
            }
            if(count($licensePositionData)){
                LicensePosition::insert($licensePositionData);
            }
        }

//        $data->permissions()->sync($request->input('permissions', []));

        return redirect()
            ->route("voyager.{$dataType->slug}.index")
            ->with([
                'message'    => __('voyager::generic.successfully_updated')." {$dataType->getTranslatedAttribute('display_name_singular')}",
                'alert-type' => 'success',
            ]);
    }

}
