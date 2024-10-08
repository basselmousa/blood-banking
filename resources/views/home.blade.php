@extends('layouts.app')

@section('homeContent')

    <div class="row1-container">
        <div class="box box-down cyan">
            <h2>Register To Be Donor</h2>
            <p>A Drop Of Blood Equals a Life.</p>
            <img class="pic" src="{{asset('img/Register.png')}}" alt="">
            <br>
            <button class="button" data-modal="modalOne">Register Now</button>
        </div>

        <div class="box red">
            <h2>Search For Blood</h2>
            <p>Let You Have a Hope..</p>
            <img class="pic" src="{{asset('img/Search.png')}}" alt="">
            <br>

            <a href="{{ route('search') }}">
                <button class="button">Search For Blood</button>
            </a>
        </div>

        <div class="box box-down blue">
            <h2>Request For Blood</h2>
            <p>We Will Help You To Find Your Donor.</p>
            <img class="pic" src="{{asset('img/Request.png')}}" alt="">
            <br>
            <button class="button" data-modal="modalTwo">Request For Blood</button>
        </div>
    </div>
    <div class="row2-container">
        <div class="box orange">
            <h2>Request For Home Donation</h2>
            <p>If You Can't Come, We Will <br>Come To You...</p>
            <img class="pic" src="{{asset('img/home.png')}}" alt="">
            <br>
            <button class="button" data-modal="modalThree">Request For <br> Home Donation</button>
        </div>
    </div>


    <!-- FOOOOOOOOOOOOOOOOOOOOOOOOOOOOORRRRRRRRRRRMMMMMMMMMMMMMMM-->
    <div id="modalOne" class="modal">
        <div class="modal-content">
            <div class="contact-form">
                <a class="close">&times;</a>
                <form method="post" action="{{ route('add-donor') }}">

                    @csrf
                    <h2>Register To Be Donor</h2>
                    <div>
                        <label>Full name: </label>
                        <input class="fname @error('full_name') is-invalid @enderror" type="text" name="full_name"
                              value="{{ old('full_name') }}" placeholder="Full Name">
                        @error('full_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        <label>ID Number: </label>
                        <input type="text" class="@error('id_number') is-invalid @enderror" name="id_number"
                             value="{{ old('id_number') }}"  placeholder="ID Number">
                        @error('id_number')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        <label>Email: </label>
                        <input type="email" class="@error('email') is-invalid @enderror" name="email"
                              value="{{ old('email') }}" placeholder="Email">
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        <label>Phone Number: </label>
                        <input type="text" class="@error('phone_number') is-invalid @enderror" name="phone_number"
                              value="{{ old('phone_number') }}" placeholder="Phone number">
                        @error('phone_number')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        <br>
                        <br>
                        <label class="cooontainer">Share my number with patiens.
                            <input type="radio" value="0" {{ old('share') ==  0 ? 'checked': '' }} name="share">
                            <span class="checkmark @error('share') is-invalid @enderror"></span>
                        </label>
                        <label class="cooontainer"> Don't share my number with patiens.
                            <input type="radio" value="1"  {{ old('share') ==  1 ? 'checked': '' }} name="share">
                            <span class="checkmark @error('share') is-invalid @enderror"></span>
                        </label>
                        @error('share')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        <label for="Gender">Your Gender:</label>
                        <select class="@error('gender') is-invalid @enderror" name="gender" id="Gender">
                            <option value="0"  >--Select Your Gender--</option>
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                        @error('gender')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        <label for="birthday">Date Of Birth:</label>
                        <input type="date" class="@error('birthday') is-invalid @enderror" id="birthday" name="birthday"
                             value="{{ old('birthday') }}"  style="width: 500px; border-radius: 3%;">
                        @error('birthday')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        <label for="blood">Blood Group:</label>
                        <select name="blood" class="@error('blood') is-invalid @enderror" id="blood">
                            <option value="0">--Select Your Blood Group--</option>
                            <option value="A+" {{ old('blood') == 'A+'? 'selected' : '' }}>A+</option>
                            <option value="AB+" {{ old('blood') == 'AB+' ? 'selected' : '' }}>AB+</option>
                            <option value="A-" {{ old('blood') =='A-' ? 'selected' : '' }}>A-</option>
                            <option value="AB-" {{ old('blood') == 'AB-' ? 'selected' : '' }}>AB-</option>
                            <option value="B+" {{ old('blood') == 'B+'? 'selected' : '' }}>B+</option>
                            <option value="B-" {{ old('blood') == 'B-'? 'selected' : '' }}>B-</option>
                            <option value="O+" {{ old('blood') == 'O+'? 'selected' : '' }}>O+</option>
                            <option value="O-" {{ old('blood') == 'O-'? 'selected' : '' }}>O-</option>
                        </select>
                        @error('blood')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        <label for="Date">Last Donated Date:</label>
                        <input type="date" class="@error('date') is-invalid @enderror" id="date" name="date"
                              value="{{ old('date') }}" style="width: 500px; border-radius: 3%;">
                        @error('date')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        <div class="input-block">
                            <label for="Country">Country:</label>
                            <select style="width: 180px;" class="@error('country') is-invalid @enderror" name="country" id="Country">
                                <option value="">--Your Country--</option>
                                <option {{ old('country') == 'Jordan' ? 'selected' : '' }} value="Jordan">Jordan</option>
                            </select>
                        </div>

                        <div class="input-block">
                            <label for="City">City:</label>
                            <select style="width: 180px;" class="@error('city') is-invalid @enderror" name="city" id="City">
                                <option value="">--Your City--</option>
                                <option {{ old('city') == 'Amman' ? 'selected' : '' }} value="Amman">Amman</option>
                                <option {{ old('city') == 'Zarqaa' ? 'selected' : '' }} value="Zarqaa">Zarqaa</option>
                                <option {{ old('city') == 'Mafraq' ? 'selected' : '' }} value="Mafraq">Mafraq</option>
                                <option {{ old('city') == 'Irbid' ? 'selected' : '' }} value="Irbid">Irbid</option>
                                <option {{ old('city') == 'Ajloun' ? 'selected' : '' }} value="Ajloun">Ajloun</option>
                                <option {{ old('city') == 'Jerash' ? 'selected' : '' }} value="Jerash">Jerash</option>
                                <option {{ old('city') == 'As-Salt' ? 'selected' : '' }} value="As-Salt">As-Salt</option>
                                <option {{ old('city') == 'Madaba' ? 'selected' : '' }} value="Madaba">Madaba</option>
                                <option {{ old('city') == 'Karak' ? 'selected' : '' }} value="Karak">Karak</option>
                                <option {{ old('city') == 'Tafilah' ? 'selected' : '' }} value="Tafilah">Tafilah</option>
                                <option {{ old('city') == "Ma'an" ? 'selected' : '' }} value="Ma'an">Ma'an</option>
                                <option {{ old('city') == 'Aqaba' ? 'selected' : '' }} value="Aqaba">Aqaba</option>
                            </select>

                        </div>


                        <span>Do You Suffer From Any Diseases?:</span>
                        <div>
                            <textarea name="diseases" class="@error('diseases') is-invalid @enderror"
                                      placeholder="Write Them Here Please....." rows="6"
                                      style="width: 520px;height: 100px">{{ old('diseases') }}</textarea>
                        </div>
                        @error('diseases')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        <button type="submit">Submit</button>
                    </div>
                </form>

            </div>
        </div>
    </div>



    <div id="modalTwo" class="modal">
        <div class="modal-content">
            <div class="contact-form">
                <a class="close">&times;</a>
                <form method="post" action="{{ route('add-patient') }}">
                    @csrf
                    <h2>Patient Registration</h2>
                    <div>
                        <label>Full name: </label>
                        <input class="fname @error('full_name') is-invalid @enderror"
                               type="text" name="full_name"
                               value="{{ old('full_name') }}"
                               placeholder="Full Name">

                        @error('full_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        <label>ID Number: </label>
                        <input type="text" class="@error('id_number') is-invalid @enderror"
                               name="id_number"
                               value="{{ old('id_number') }}"
                               placeholder="ID Number">


                        @error('id_number')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        <label>Phone Number: </label>
                        <input type="text" class="@error('phone_number') is-invalid @enderror"
                               name="phone_number"
                               value="{{ old('phone_number') }}"
                               placeholder="Phone number">

                        @error('phone_number')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        <label for="Gender">Your Gender:</label>
                        <select name="gender" class="@error('gender') is-invalid @enderror" id="Gender">
                            <option value="Select">--Select Your Gender--</option>
                            <option {{ old('gender') == 'Male'?'selected' : '' }} value="Male">Male</option>
                            <option {{ old('gender') == 'Female'?'selected' : '' }} value="Female">Female</option>
                        </select>

                        @error('gender')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        <label for="birthday">Date Of Birth:</label>
                        <input type="date" class="@error('birthday') is-invalid @enderror"
                               value="{{ old('') }}"
                               id="birthday" name="birthday" style="width: 500px; border-radius: 3%;">

                        @error('birthday')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        <label for="Blood">Blood Group:</label>
                        <select name="blood" class="@error('blood') is-invalid @enderror" id="Blood">
                            <option value="">--Select Your Blood Group--</option>
                            <option {{ old('blood') == 'A+' ? 'selected' : '' }} value="A+">A+</option>
                            <option {{ old('blood') == 'AB+' ? 'selected' : '' }} value="AB+">AB+</option>
                            <option {{ old('blood') == 'A-' ? 'selected' : '' }} value="A-">A-</option>
                            <option {{ old('blood') == 'AB-' ? 'selected' : '' }} value="AB-">AB-</option>
                            <option {{ old('blood') == '"B+"' ? 'selected' : '' }} value="B+">B+</option>
                            <option {{ old('blood') == 'B-' ? 'selected' : '' }} value="B-">B-</option>
                            <option {{ old('blood') == 'O+' ? 'selected' : '' }} value="O+">O+</option>
                            <option {{ old('blood') == 'O-' ? 'selected' : '' }} value="O-">O-</option>
                        </select>

                        @error('blood')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        <label for="Country">Country:</label>
                        <select style="width: 180px;" class="@error('country') is-invalid @enderror" name="country" id="Country">
                            <option value="">--Your Country--</option>
                            <option {{ old('country') == 'Jordan' ? 'selected' : '' }} value="Jordan">Jordan</option>
                        </select>

                        @error('country')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        <label for="City">City:</label>
                        <select style="width: 180px;" class="@error('city') is-invalid @enderror" name="city" id="City">
                            <option value="">--Your City--</option>
                            <option {{ old('city') == 'Amman' ? 'selected' : '' }} value="Amman">Amman</option>
                            <option {{ old('city') == 'Zarqaa' ? 'selected' : '' }} value="Zarqaa">Zarqaa</option>
                            <option {{ old('city') == 'Mafraq' ? 'selected' : '' }} value="Mafraq">Mafraq</option>
                            <option {{ old('city') == 'Irbid' ? 'selected' : '' }} value="Irbid">Irbid</option>
                            <option {{ old('city') == 'Ajloun' ? 'selected' : '' }} value="Ajloun">Ajloun</option>
                            <option {{ old('city') == 'Jerash' ? 'selected' : '' }} value="Jerash">Jerash</option>
                            <option {{ old('city') == 'As-Salt' ? 'selected' : '' }} value="As-Salt">As-Salt</option>
                            <option {{ old('city') == 'Madaba' ? 'selected' : '' }} value="Madaba">Madaba</option>
                            <option {{ old('city') == 'Karak' ? 'selected' : '' }} value="Karak">Karak</option>
                            <option {{ old('city') == 'Tafilah' ? 'selected' : '' }} value="Tafilah">Tafilah</option>
                            <option {{ old('city') == "Ma'an" ? 'selected' : '' }} value="Ma'an">Ma'an</option>
                            <option {{ old('city') == 'Aqaba' ? 'selected' : '' }} value="Aqaba">Aqaba</option>
                        </select>

                        @error('city')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        <br>
                        <label for="Address">Detailed Address:</label>
                        <div>
                            <textarea name="address" class="@error('address') is-invalid @enderror" placeholder="Write Your Address Here....." rows="4"
                                      style="width: 520px;;height: 100px">{{ old('address') }}</textarea>

                            @error('address')
                            <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                            @enderror
                        </div>

                        <button type="submit">Submit</button>
                    </div>

                </form>
            </div>
        </div>
    </div>


    <div id="modalThree" class="modal">
        <div class="modal-content">
            <div class="contact-form">
                <a class="close">&times;</a>
                <form method="post" action="{{ route('add-home-donor') }}">
                    @csrf
                    <h2>Home Donation</h2>
                    <div>
                        <label>Full name: </label>
                        <input class="fname @error('full_name') is-invalid @enderror"
                               value="{{ old('full_name') }}"
                               type="text" name="full_name" placeholder="Full Name">

                        @error('full_name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        <label>ID Number: </label>
                        <input type="text" class="@error('id_number') is-invalid @enderror"
                               value="{{ old('id_number') }}"
                               name="id_number" placeholder="ID Number">


                        @error('id_number')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        <label>Phone Number: </label>
                        <input type="text" class="@error('phone_number') is-invalid @enderror"
                               {{ old('phone_number') }}
                               name="phone_number" placeholder="Phone number">

                        @error('phone_number')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        <label for="Gender">Your Gender:</label>
                        <select name="gender" class="@error('gender') is-invalid @enderror" id="Gender">
                            <option value="Select">--Select Your Gender--</option>
                            <option {{ old('gender') == 'Male' ? 'selected' : '' }} value="Male">Male</option>
                            <option {{ old('gender') == 'Female' ? 'selected' : '' }} value="Female">Female</option>
                        </select>

                        @error('gender')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        <label for="birthday">Date Of Birth:</label>
                        <input type="date" class="@error('birthday') is-invalid @enderror"
                               value="{{ old('birthday') }}"
                               id="birthday" name="birthday" style="width: 500px; border-radius: 3%;">

                        @error('birthday')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        <label for="Blood">Blood Group:</label>
                        <select name="blood" class="@error('blood') is-invalid @enderror" id="Blood">
                            <option value="">--Select Your Blood Group--</option>
                            <option {{ old('blood') == 'A+' ? 'selected' : '' }} value="A+">A+</option>
                            <option {{ old('blood') == 'AB+' ? 'selected' : '' }} value="AB+">AB+</option>
                            <option {{ old('blood') == 'A-' ? 'selected' : '' }} value="A-">A-</option>
                            <option {{ old('blood') == 'AB-' ? 'selected' : '' }} value="AB-">AB-</option>
                            <option {{ old('blood') == 'B+' ? 'selected' : '' }} value="B+">B+</option>
                            <option {{ old('blood') == 'B-' ? 'selected' : '' }} value="B-">B-</option>
                            <option {{ old('blood') == 'O+' ? 'selected' : '' }} value="O+">O+</option>
                            <option {{ old('blood') == 'O-' ? 'selected' : '' }} value="O-">O-</option>
                        </select>

                        @error('blood')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        <label for="Country">Country:</label>
                        <select style="width: 180px;" class="@error('country') is-invalid @enderror" name="country" id="Country">
                            <option value="">--Your Country--</option>
                            <option {{ old('country') == 'Jordan' ? 'selected' : '' }} value="Jordan">Jordan</option>
                        </select>

                        @error('country')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror

                        <label for="City">City:</label>
                        <select style="width: 180px;" class="@error('city') is-invalid @enderror" name="city" id="City">
                            <option value="">--Your City--</option>
                            <option {{ old('city') == 'Amman' ? 'selected' : '' }} value="Amman">Amman</option>
                            <option {{ old('city') == 'Zarqaa' ? 'selected' : '' }} value="Zarqaa">Zarqaa</option>
                            <option {{ old('city') == 'Mafraq' ? 'selected' : '' }} value="Mafraq">Mafraq</option>
                            <option {{ old('city') == 'Irbid' ? 'selected' : '' }} value="Irbid">Irbid</option>
                            <option {{ old('city') == 'Ajloun' ? 'selected' : '' }} value="Ajloun">Ajloun</option>
                            <option {{ old('city') == 'Jerash' ? 'selected' : '' }} value="Jerash">Jerash</option>
                            <option {{ old('city') == 'As-Salt' ? 'selected' : '' }} value="As-Salt">As-Salt</option>
                            <option {{ old('city') == 'Madaba' ? 'selected' : '' }} value="Madaba">Madaba</option>
                            <option {{ old('city') == 'Karak' ? 'selected' : '' }} value="Karak">Karak</option>
                            <option {{ old('city') == 'Tafilah' ? 'selected' : '' }} value="Tafilah">Tafilah</option>
                            <option {{ old('city') == "Ma'an" ? 'selected' : '' }} value="Ma'an">Ma'an</option>
                            <option {{ old('city') == 'Aqaba' ? 'selected' : '' }} value="Aqaba">Aqaba</option>
                        </select>

                        @error('city')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                        <br>
                        <label for="Address">Detailed Address:</label>
                        <div>
                            <textarea name="address" class="@error('address') is-invalid @enderror" placeholder="Write Your Address Here....." rows="4"
                                      style="width: 520px;height: 100px">{{ old('address') }}</textarea>

                            @error('address')
                            <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                            @enderror
                        </div>

                        <button type="submit">Submit</button>
                    </div>


                </form>
            </div>
        </div>
    </div>


@endsection
