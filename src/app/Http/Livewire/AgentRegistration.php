<?php

namespace App\Http\Livewire;

use App\Models\Agent;
use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads;

class AgentRegistration extends Component
{
    use WithFileUploads;

    public $bid_file_path, $nid_file_path, $passport_file_path, $full_photo_file_path, $cv_file_path;

    public $name,$father_name, $mother_name, $email, $mobile, $secondary_mobile, $bid, $present_address_house, $permanent_address_house;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:agents,email',
        'mobile' => 'required|min:11',
    ];

    public function register()
    {
        $this->validate();

        $bid_file_path = $this->bid_file_path->store('bid_file_path', 'public');
        $nid_file_path = $this->nid_file_path->store('nid_file_path', 'public');
        $passport_file_path = $this->passport_file_path->store('passport_file_path', 'public');
        $full_photo_file_path = $this->full_photo_file_path->store('full_photo_file_path', 'public');
        $cv_file_path = $this->cv_file_path->store('cv_file_path', 'public');

        $agent = Agent::create([
            'code' => date('md').rand(100,999),
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'bid_file_path' => $bid_file_path,
            'nid_file_path' => $nid_file_path,
            'passport_file_path' =>$passport_file_path,
            'full_photo_file_path' => $full_photo_file_path,
            'cv_file_path' =>$cv_file_path,
        ]);

        session()->flash('message', 'Registration successfully done.');
    }

    public function render()
    {
        return view('livewire.agent-registration');
    }
}
