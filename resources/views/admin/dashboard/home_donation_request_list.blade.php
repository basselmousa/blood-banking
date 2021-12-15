@extends('admin.dashboard.layouts.app')
@section('adminContent')

    <h1 style="font-size: 55px; text-align: center;">Request List</h1>
    <br>

    <table id="customers">
        <tr>
            <th>ID</th>
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

    </table>
@endsection
