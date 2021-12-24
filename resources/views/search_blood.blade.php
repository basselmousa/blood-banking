@extends('layouts.app')
@section('css' )
    <link rel="stylesheet" type="text/css" href="{{asset('css/Styles/sstt.css')}}"/>
@endsection
@section('homeContent')
    <form method="post" action="{{ route('search.post') }}" class="example" style="margin:auto;max-width:300px">
        @csrf
        <input type="text" placeholder="Search by blood group" name="blood">
        <button type="submit"><i class="fa fa-search"></i></button>
    </form>

    <br>
    <br>

    <table id="customers">
        <tr>

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

        @foreach($bloods as $blood)
            <tr>
                <td>{{ $blood->full_name }}</td>
                <td>{{ $blood->id_number }}</td>
                <td>{{ $blood->email }}</td>
                <td>{{ $blood->phone_number }}</td>
                <td>{{ $blood->gender }}</td>
                <td>{{ $blood->date_of_birth }}</td>
                <td>{{ $blood->blood_group }}</td>
                <td>{{ $blood->last_donation_date }}</td>
                <td>{{ $blood->diseases }}</td>
            </tr>
        @endforeach

    </table>

@endsection
