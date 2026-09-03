<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public $email, $password;

    public function login()
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            if(Auth::user()->status  == 'A'){
                session()->regenerate();
                if(!empty(Auth::user()->employer_id)) {
                    return redirect()->intended('/'); // or wherever
                }else{
                    return redirect()->intended('panel'); // or wherever

                }
            }else{
               $this->addError('email', 'Sorry! You are not authorized to login.');
            }

        }
        $this->addError('email', 'Invalid credentials.');
    }

    public function render()
    {
        return view('livewire.login');
    }
}
