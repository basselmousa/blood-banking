@extends('admin.dashboard.layouts.app')
@section('adminContent')
    <h1 style="font-size: 55px; text-align: center;">Patient List</h1>
    <br>

    <table id="customers">
        <tr >

            <th>Full Name</th>
            <th>ID Number</th>
            <th>Phone Number</th>
            <th>Gender</th>
            <th>Date Of Birth</th>
            <th>Blood Group</th>
            <th>Country</th>
            <th>City</th>
            <th>Detailed Address</th>
        </tr>

        @foreach($patients as $patient)

            <tr>

                <td>{{ $patient->full_name }}</td>
                <td>{{ $patient->id_number }}</td>
                <td>{{ $patient->phone_number }}</td>
                <td>{{ $patient->gender }}</td>
                <td>{{ $patient->date_of_birth }}</td>
                <td>{{ $patient->blood_group }}</td>
                <td>{{ $patient->country }}</td>
                <td>{{ $patient->city }}</td>
                <td>{{ $patient->address }}</td>
            </tr>

        @endforeach

    </table>
@endsection
