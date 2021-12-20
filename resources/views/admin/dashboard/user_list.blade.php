@extends('admin.dashboard.layouts.app')
@section('adminContent')

    <h1 style="font-size: 55px; text-align: center;">Users List</h1>
    <br>

    <table id="customers">
        <tr>

            <th>Full Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Phone Number</th>


        </tr>

        @foreach($users as $user)
            <tr>

                <td>{{ $user->full_name }}</td>
                <td>{{ $user->username }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->phone_number }}</td>


            </tr>
        @endforeach

    </table>
@endsection
