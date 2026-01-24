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
            'rhesus_factor' => ['nullable', 'in:+,-'],
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
            'rhesus_factor' => $request->rhesus_factor ?? '+',
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
            'share' => ['required'],
            'weight' => ['nullable', 'numeric', 'min:50'],
            'height' => ['nullable', 'numeric', 'min:100'],
            'blood_pressure' => ['nullable', 'string'],
            'hemoglobin_level' => ['nullable', 'numeric', 'min:8', 'max:20'],
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
            'share' => $request->share,
            'weight' => $request->weight,
            'height' => $request->height,
            'blood_pressure' => $request->blood_pressure,
            'hemoglobin_level' => $request->hemoglobin_level,
            'last_health_checkup' => now(),
            'is_deferred' => false,
        ]);
    }
}
