@extends('layouts.app')

@section('homeContent')
    <table id="customers">
        <tr>
            <th>Full Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Phone Number</th>

        </tr>

        <tr>
            <td>{{ auth('web')->user()->full_name }}</td>
            <td>{{ auth('web')->user()->username }}</td>
            <td>{{ auth('web')->user()->email }}</td>
            <td>{{ auth('web')->user()->phone_number }}</td>
        </tr>

    </table>
@endsection
