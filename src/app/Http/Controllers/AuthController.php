<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        return view('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/login');
    }

    public function registration(Request $request, $form)
    {
        return view('registration', ['form'=>ucfirst($form)]);
    }

}
