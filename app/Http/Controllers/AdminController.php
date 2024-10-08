<?php

namespace App\Http\Controllers;

use App\Http\Traits\AddPatient;
use App\Models\Admin;
use App\Models\Donor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    //

    use AddPatient;
    public function __construct()
    {

        $this->middleware(['auth:admin']);
    }

    public function index()
    {
//        dd(auth('admin')->user());
        return view('admin.dashboard.home');
    }

    public function donor_list()
    {
        $donors = Donor::all();
        return view('admin.dashboard.donor_list', compact('donors'));

    }

    public function patient_list()
    {
        $patients = Patient::all()->where('type', '=', 'patient');

        return view('admin.dashboard.patient_list', compact('patients'));
    }

    public function user_list()
    {
        $users = User::all();
        return view('admin.dashboard.user_list', compact('users'));
    }

    public function add_donor_or_patient()
    {
        return view('admin.dashboard.add_donor_or_patient');
    }

    public function home_donation_request_list()
    {
        $requests = Patient::all()->where('type', '=', 'donor');
        return view('admin.dashboard.home_donation_request_list', compact('requests'));
    }

    public function delete_donor_or_patient()
    {
        return view('admin.dashboard.delete_donor_or_patient');
    }

    public function add_donor(Request $request)
    {
        $this->addDonor($request);

        return redirect()->route('admin.home');
    }

    public function add_patient(Request $request)
    {
        $this->addPatient($request, 'patient');
        return redirect()->route('admin.home');
    }

    public function delete_donor_or_patient_action(Request $request)
    {
        $request->validate([
            'id_number' => 'required',
            'from_model' => 'required|not_in:0'
        ]);

        switch ($request->from_model) {
            case '1':
                Donor::where('id_number', '=', $request->id_number)->delete();
                break;
            case '2':
                Patient::where('id_number', '=', $request->id_number)->delete();
                break;
        }
        return redirect()->route('admin.home');
    }


    public function create_admin()
    {
        return view('admin.dashboard.create_admin');
    }

    public function create_admin_post(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:10'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        Admin::create([
            'full_name' => $request['full_name'],
            'username' => $request['username'],
            'phone_number' => $request['phone_number'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
        ]);
        return redirect()->route('admin.home');
    }

}
