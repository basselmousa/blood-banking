<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    //

    public function index()
    {
        return view('admin.dashboard.home');
    }

    public function donor_list()
    {
        return view('admin.dashboard.donor_list');

    }

    public function patient_list()
    {
        return view('admin.dashboard.patient_list');
    }

    public function user_list()
    {
        return view('admin.dashboard.user_list');
    }

    public function add_donor_or_patient()
    {
        return view('admin.dashboard.add_donor_or_patient');
    }

    public function home_donation_request_list()
    {
        return view('admin.dashboard.home_donation_request_list');
    }

    public function delete_donor_or_patient()
    {
        return view('admin.dashboard.delete_donor_or_patient');
    }
}
