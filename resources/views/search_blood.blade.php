@extends('layouts.app')
@section('css' )
    <link rel="stylesheet" type="text/css" href="{{asset('css/Styles/sstt.css')}}"/>
@endsection
@section('homeContent')
    <form method="post" action="{{ route('search.post') }}" class="example" style="margin:auto;max-width:300px">
        @csrf

        <h1 style="font-size: 40px; text-align: center; font-weight:300; font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif">Insert Any Blood Group To Search..</h1>
        <br>
        <br>
        <br>

        <div style="text-align: center; float: left; margin-left: 33%;" >
            <select class="classic  @error('blood') is-invalid @enderror" name="blood">
                <option value="" disabled selected> Select Blood Group </option>
                <option value="A+">A+</option>
                <option value="AB+">AB+</option>
                <option value="A-">A-</option>
                <option value="AB-">AB-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
            </select>
            @error('blood')
            <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
            @enderror
        </div>

        <div style="text-align: center; float: right; margin-right: 33%;">
            <select class="classic @error('city') is-invalid @enderror" name="city">
                <option value="" disabled selected> Select A City</option>
                <option value="all">All</option>
                <option value="Amman">Amman</option>
                <option value="Zarqaa">Zarqaa</option>
                <option value="Mafraq">Mafraq</option>
                <option value="Irbid">Irbid</option>
                <option value="Ajloun">Ajloun</option>
                <option value="Jerash">Jerash</option>
                <option value="As-Salt">As-Salt</option>
                <option value="Madaba">Madaba</option>
                <option value="Karak">Karak</option>
                <option value="Tafilah">Tafilah</option>
                <option value="Ma'an">Ma'an</option>
                <option value="Aqaba">Aqaba</option>

            </select>

            @error('city')
            <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
            @enderror
        </div>

        <button class="btn btn-primary" type="submit">Search</button>

    </form>

    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>


    <table id="customers">
        <tr>

            <th>Full Name</th>
            <th>ID Number</th>
            <th>Email</th>

            <th>Gender</th>
            <th>Date Of Birth</th>
            <th>Blood Group</th>
            <th>Last Donated Date</th>
            <th>Diseases</th>
            <th>Contact</th>
        </tr>

        @foreach($bloods as $blood)
            <tr>
                <td>{{ $blood->full_name }}</td>
                <td>{{ $blood->id_number }}</td>
                <td>{{ $blood->email }}</td>

                <td>{{ $blood->gender }}</td>
                <td>{{ $blood->date_of_birth }}</td>
                <td>{{ $blood->blood_group }}</td>
                <td>{{ $blood->last_donation_date }}</td>
                <td>{{ $blood->diseases }}</td>
                <td>
                    @if($blood->share == 0)
                        {{ $blood->phone_number }}
                    @else
                        <button class="btn btn-primary"
                        onclick="event.preventDefault();
                        document.getElementById('contact-form-{{$blood->id}}').submit();
                        "
                        >Contact</button>
                        <form id="contact-form-{{ $blood->id }}" action="{{ route('contact-user', $blood->id) }}" method="post" class="d-none">
                        @csrf

                        </form>
                    @endif

                </td>
            </tr>
        @endforeach

    </table>

@endsection
