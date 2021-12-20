@extends('admin.dashboard.layouts.app')
@section('adminContent')

    <h1 style="font-size: 55px; text-align: center;">Request List</h1>
    <br>

    <table id="customers">
        <tr>

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


        @foreach($requests as $request)
            <tr>

                <td>{{ $request->full_name }}</td>
                <td>{{ $request->id_number }}</td>
                <td>{{ $request->phone_number }}</td>
                <td>{{ $request->gender }}</td>
                <td>{{ $request->date_of_birth }}</td>
                <td>{{ $request->blood_group }}</td>
                <td>{{ $request->country }}</td>
                <td>{{ $request->city }}</td>
                <td>{{ $request->address }}</td>
            </tr>

        @endforeach


    </table>
@endsection
