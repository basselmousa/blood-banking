@extends('admin.dashboard.layouts.app')
@section('adminContent')
    <h1 style="font-size: 55px; text-align: center;">Donor List</h1>

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
            <th>Country</th>
            <th>City</th>
            <th>Diseases</th>
            <th>Actions</th>
        </tr>

        @foreach($donors as $donor)
            <tr>

                <td>{{ $donor->full_name }}</td>
                <td>{{ $donor->id_number }}</td>
                <td>{{ $donor->email }}</td>
                <td>{{ $donor->phone_number }}</td>
                <td>{{ $donor->gender }}</td>
                <td>{{ $donor->date_of_birth }}</td>
                <td>{{ $donor->blood_group }}</td>
                <td>{{ $donor->last_donation_date }}</td>
                <td>{{ $donor->country }}</td>
                <td>{{ $donor->city }}</td>
                <td>{{ $donor->diseases }}</td>
                <td>
                    <form action="{{ route('admin.donors.update-donation-date', ['donor' => $donor->id]) }}" method="POST" style="display: inline-block">
                        @csrf
                        <button type="submit" class="btn btn-primary">Update Last Donation</button>
                    </form>
                </td>
            </tr>

        @endforeach


    </table>


@endsection
