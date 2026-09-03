<?php

namespace App\Http\Controllers\Admin;

use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerAuthController as BaseVoyagerAuthController;

class VoyagerAuthController extends BaseVoyagerAuthController
{
    public function login()
    {
        return redirect()->route('web.login');
    }
}
