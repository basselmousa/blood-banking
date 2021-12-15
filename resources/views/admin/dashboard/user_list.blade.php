@extends('admin.dashboard.layouts.app')
@section('adminContent')

    <h1 style="font-size: 55px; text-align: center;">Users List</h1>
    <br>

    <table id="customers">
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Phone Number</th>
            <th>Password</th>

        </tr>

    </table>
@endsection
