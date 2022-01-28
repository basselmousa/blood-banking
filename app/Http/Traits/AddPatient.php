<?php


namespace App\Http\Traits;


use App\Models\Donor;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;


trait AddPatient
{
    public function addPatient(Request $request, $type)
    {
        $request->validate([
            'full_name' => ['required'],
            'id_number' => ['required','unique:patients,id_number'],
            'phone_number' => ['required','unique:patients,phone_number'],
            'gender' => ['required','not_in:0'],
            'birthday' => ['required'],
            'blood' => ['required','not_in:0'],
            'country' => ['required','not_in:0'],
            'city' => ['required','not_in:0'],
            'address' => ['required'],
        ]);

        Patient::create([
            'type' => $type,
            'full_name' => $request->full_name,
            'id_number' => $request->id_number,
            'phone_number' => $request->phone_number,
            'gender' => $request->gender,
            'date_of_birth' => $request->birthday,
            'blood_group' => $request->blood,
            'country' => $request->country,
            'city' => $request->city,
            'address' => $request->address,
        ]);
    }

    public function addDonor(Request $request)
    {
        $request->validate([
            'full_name' => ['required'],
            'id_number' => ['required', 'unique:donors,id_number'],
            'email' => ['required', 'unique:donors,email'],
            'phone_number' => ['required', 'unique:donors,phone_number'],
            'gender' => ['required','not_in:0'],
            'country' => ['required','not_in:0'],
            'city' => ['required','not_in:0'],
            'birthday' => ['required','before:'. Carbon::today()->subYears(18)],
            'blood' => ['required','not_in:0'],
            'date' => ['required','before:'.Carbon::today()->subMonths(6)],
            'diseases' => ['required'],
            'share' => ['required']
        ]);

        Donor::create([
            'full_name' => $request->full_name,
            'id_number' => $request->id_number,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'gender' => $request->gender,
            'country' => $request->country,
            'city' => $request->city,
            'date_of_birth' => $request->birthday,
            'blood_group' => $request->blood,
            'last_donation_date' => $request->date,
            'diseases' => $request->diseases,
            'share' => $request->share
        ]);
    }
}
