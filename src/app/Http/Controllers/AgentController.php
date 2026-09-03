<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AgentController extends Controller
{

    public function agentRegistration(Request $request)
    {
        return view('agent-registration');
    }

    public function index(Request $request)
    {
        return view('home');
    }

}
