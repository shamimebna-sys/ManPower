<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\CommonClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use TCG\Voyager\Database\Schema\SchemaManager;
use TCG\Voyager\Events\BreadAdded;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerBaseController as VoyagerBaseController;

class VoyagerProfileController extends VoyagerBaseController
{
    public function index1(Request $request){
        $data = [];
        return view('vendor.voyager.profile.edit-add', ['data'=>$data]);
    }


    public function index(Request $request)
    {
        $id = Auth::user()->agent_id;

        $slug = 'agents';

        $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();

    /*        if (strlen($dataType->model_name) != 0) {
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
            }*/

        $dataTypeContent = DB::table($dataType->name)->where('id', $id)->first();


        foreach ($dataType->editRows as $key => $row) {
            $dataType->editRows[$key]['col_width'] = isset($row->details->width) ? $row->details->width : 100;
        }

        // If a column has a relationship associated with it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'edit');

        // Check permission
//        $this->authorize('edit', $dataTypeContent);

        // Check if BREAD is Translatable
        $isModelTranslatable = is_bread_translatable($dataTypeContent);

        // Eagerload Relations
        $this->eagerLoadRelations($dataTypeContent, $dataType, 'edit', $isModelTranslatable);

        $view = 'vendor.voyager.profile.edit-add';

        return Voyager::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }

}
