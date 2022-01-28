<?php

namespace App\Http\Controllers;

use App\Http\Traits\AddPatient;
use App\Mail\ContactMe;
use App\Mail\ContactUserForMe;
use App\Models\Donor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{

    use AddPatient;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:web')->except('contact');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function search(Request $request)
    {

        if ($request->method() == 'GET'){
            $bloods = [];
        }
        else{
            $request->validate([
                'blood' => 'required',
                'city' => 'required'
            ]);
            if ($request->city == 'all'){
                $bloods = Donor::all()->where('blood_group' , '=', $request->blood);
            }
            else{
                $bloods = Donor::all()->where('blood_group' , '=', $request->blood)->where('city', '=', $request->city);
            }
        }
        return view('search_blood', compact('bloods'));
    }

    public function add_donor(Request $request)
    {
       $this->addDonor($request);

        return redirect()->route('home');
    }

    public function add_home_donor(Request $request)
    {
        $this->addPatient($request, 'donor');
        return redirect()->route('home');
    }

    public function add_patient(Request $request)
    {
        $this->addPatient($request,'patient');
        return redirect()->route('home');
    }

    public function contact(Request $request)
    {
        Mail::to(config('mail.from.address'))->send(new ContactMe($request->all()));
        return redirect('/');
    }


    public function contact_user(Request $request, Donor $id)
    {
        Mail::to(config('mail.from.address'))->send(new ContactUserForMe($id));
        return redirect()->route('home');
    }

    public function personal_information()
    {
        return view('personal_information');
    }

}
