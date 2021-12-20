<?php

namespace App\Http\Controllers;

use App\Http\Traits\AddPatient;
use App\Models\Donor;
use App\Models\Patient;
use Illuminate\Http\Request;

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
        $this->middleware('auth');
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

    public function search()
    {
        return view('search_blood');
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

}
