@extends('admin.dashboard.layouts.app')
@section('adminContent')

    <div class="row1-container">
        <div class="box box-down cyan">
            <h2>Users List</h2>
            <img class="pic" src="{{asset('img/customer.png')}}" alt="">
            <br>
            <a href="{{ route('admin.users') }}">
                <button class="button" >Users List</button>
            </a>

        </div>

        <div class="box red">
            <h2>Donors List</h2>
            <img class="pic" src="{{asset('img/donor.png')}}" alt="">
            <br>
            <a href="{{ route('admin.donors') }}">
                <button class="button">Donors List</button>
            </a>
        </div>

        <div class="box red">
            <h2>Patients List</h2>
            <img class="pic" src="{{asset('img/shortlist.png')}}" alt="">
            <br>
            <a href="{{ route('admin.patients') }}">
                <button class="button">Patients List</button>
            </a>
        </div>

        <div class="box box-down blue">
            <h2>Delete Donors/Petients</h2>
            <img class="pic" src="{{asset('img/delete.png')}}" alt="">
            <br>
            <a href="{{ route('admin.delete') }}">
                <button class="button">Delete</button>
            </a>
        </div>


    </div>
    <div class="row2-container">
        <div class="box orange">
            <h2>Add <br>Donors/Patients</h2>
            <img class="pic" src="{{asset('img/add-user.png')}}" alt="">
            <br>
            <a href="{{ route('admin.add') }}">
                <button class="button" >Add</button>
            </a>
        </div>

        <div class="box orange">
            <h2>Home Donation Request List</h2>
            <img class="pic" src="{{asset('img/house (1).png')}}" alt="">
            <br>
            <a href="{{ route('admin.requests') }}">
                <button class="button">Request List</button>
            </a>
        </div>
    </div>

@endsection
