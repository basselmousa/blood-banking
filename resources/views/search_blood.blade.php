@extends('layouts.app')
@section('css' )
    <link rel="stylesheet" type="text/css" href="{{asset('css/Styles/sstt.css')}}"/>
@endsection
@section('homeContent')
    <form method="post" class="example" style="margin:auto;max-width:300px">
        @csrf
        <input type="text" placeholder="Search.." name="search2">
        <button type="submit"><i class="fa fa-search"></i></button>
    </form>

    <br>
    <br>

    <table id="customers">
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>ID Number</th>
            <th>Email</th>
            <th>Phone Number</th>
            <th>Gender</th>
            <th>Date Of Birth</th>
            <th>Blood Group</th>
            <th>Last Donated Date</th>
            <th>Diseases</th>
        </tr>

    </table>

@endsection
