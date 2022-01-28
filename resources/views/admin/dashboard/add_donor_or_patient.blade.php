@extends('admin.dashboard.layouts.app')
@section('adminContent')

    <h1 style="font-size: 35px; color: black; text-align: center;">Add A Record</h1> <br>
    <br>


    <section class="forms-section">
        <div class="forms">
            <div class="form-wrapper is-active">
                <button type="button" class="switcher switcher-login">
                    Add Donor
                    <span class="underline"></span>
                </button>

                <form method="post" action="{{ route('admin.add.donor') }}" class="form form-login">
                    @csrf
                    <fieldset>
                        <div class="input-block">
                            <label for="Fname">Full Name</label>
                            <input id="Name" class="@error('full_name') is-invalid @enderror" name="full_name" type="text" required placeholder="Full Name.." >
                            @error('full_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="input-block">
                            <label for="ID">ID Number</label>
                            <input type="text"  class="@error('id_number') is-invalid @enderror" id="idnum" name="id_number" placeholder="ID Number..">
                            @error('id_number')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="input-block">
                            <label for="Email">Email</label>
                            <input type="text" id="Email" name="email" class="@error('email') is-invalid @enderror" placeholder="Email..">
                            @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="input-block">
                            <label for="Phonenum">Phone Number</label>
                            <input type="text" id="Phonenum" class="@error('phone_number') is-invalid @enderror" name="phone_number" placeholder="Phone Number..">
                            @error('phone_number')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
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
                        <div class="input-block">
                            <label for="Gender">Gender</label>
                            <select style="width: 100%; background-color: #eef9fe; font-size: 16px;" id="Gender" class="@error('gender') is-invalid @enderror" name="gender">
                                <option value="">--Select--</option>
                                <option value="Female">Female</option>
                                <option value="Male">Male</option>
                            </select>
                            @error('gender')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="input-block">
                            <label for="date">Date Of Birth</label>
                            <input type="date" id="date" class="@error('birthday') is-invalid @enderror" name="birthday" placeholder="Date Of Birth..">
                            @error('birthday')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="input-block">
                            <label for="blood">Blood Group:</label>
                            <select style="width: 100%; background-color: #eef9fe; font-size: 16px; background-color: #eef9fe; font-size: 16px;" class="@error('blood') is-invalid @enderror" name="blood" id="blood" >
                                <option value="">--Select Your Blood Group--</option>
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

                        <div class="input-block">
                            <label for="lastdate">Last Donation Date</label>
                            <input type="date" class="@error('date') is-invalid @enderror" id="lastdate" name="date" placeholder="Last Donation Date..">
                            @error('date')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="input-block">
                            <label for="Country">Country:</label>
                            <select style="width: 100%; background-color: #eef9fe; font-size: 16px;" class="@error('country') is-invalid @enderror" name="country" id="Country">
                                <option value="">--Your Country--</option>
                                <option {{ old('country') == 'Jordan' ? 'selected' : '' }} value="Jordan">Jordan</option>
                            </select>
                            @error('country')
                            <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                            @enderror
                        </div>

                        <div class="input-block">
                            <label for="City">City:</label>
                            <select style="width: 100%; background-color: #eef9fe; font-size: 16px;" class="@error('city') is-invalid @enderror" name="city" id="City">
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
                        </div>
                        <div class="input-block">
                            <label for="Diseases">Suffering From Any Diseases?..</label> <br>
                            <textarea style="width: 100%; background-color: #eef9fe; font-size: 16px;" class="@error('diseases') is-invalid @enderror" name="diseases" placeholder="Write Them Here Please....." rows="6" ></textarea>
                            @error('diseases')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>



                    </fieldset>
                    <button  type="submit" class="btn-login">Add</button>
                </form>

            </div>


            <div class="form-wrapper">

                <button type="button" class="switcher switcher-signup">
                    Add Patient
                    <span class="underline"></span>
                </button>

                <form method="post" action="{{ route('admin.add.patient') }}" class="form form-signup">

                    @csrf
                    <fieldset>


                        <div class="input-block">
                            <label for="Fname">Full Name</label>
                            <input id="Name" class="@error('full_name') is-invalid @enderror" type="text" name="full_name" required placeholder="Full Name.." >
                            @error('full_name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="input-block">
                            <label for="ID">ID Number</label>
                            <input type="text" class="@error('id_number') is-invalid @enderror" id="idnum" name="id_number" placeholder="ID Number..">
                            @error('id_number')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                        <div class="input-block">
                            <label for="Phonenum">Phone Number</label>
                            <input type="text" class="@error('phone_number') is-invalid @enderror" id="Phonenum" name="phone_number" placeholder="Phone Number..">
                            @error('phone_number')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="input-block">
                            <label for="Gender">Gender</label>
                            <select style="width: 100%; background-color: #eef9fe; font-size: 16px;" class="@error('gender') is-invalid @enderror" id="Gender" name="gender">
                                <option value="">--Select--</option>
                                <option value="Female">Female</option>
                                <option value="Male">Male</option>
                            </select>
                            @error('gender')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="input-block">
                            <label for="date">Date Of Birth</label>
                            <input type="date" class="@error('birthday') is-invalid @enderror" id="date" name="birthday" placeholder="Date Of Birth..">
                            @error('birthday')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="input-block">
                            <label for="blood">Blood Group:</label>
                            <select style="width: 100%; background-color: #eef9fe; font-size: 16px;" class="@error('blood') is-invalid @enderror" name="blood" id="Blood" >
                                <option value="">--Select Your Blood Group--</option>
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

                        <div class="input-block">
                            <label for="Country">Country:</label>
                            <select style="width: 100%; background-color: #eef9fe; font-size: 16px;" style="width: 180px;" class="@error('country') is-invalid @enderror" name="country" id="Country" >
                                <option value="">--Your Country--</option>
                                <option value="Jordan">Jordan</option>
                            </select>
                            @error('country')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="input-block">
                            <label for="City">City:</label>
                            <select style="width: 100%; background-color: #eef9fe; font-size: 16px;" style="width: 180px;" class="@error('city') is-invalid @enderror" name="city" id="City" >
                                <option value="">--Your City--</option>
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

                        <div class="input-block">
                            <label for="address">Detailed Address..</label> <br>
                            <textarea style="width: 100%; background-color: #eef9fe; font-size: 16px;" class="@error('address') is-invalid @enderror" name="address" placeholder="Write The Address Here Please....." rows="6" style="width: 520px;"></textarea>
                            @error('address')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                    </fieldset>

                    <button type="submit" class="btn-signup">Add</button>
                </form>

            </div>
        </div>
    </section>

    <br>
    <br>
    <br>
    <br>
    <br>
    <br>

    <script>
        const switchers = [...document.querySelectorAll('.switcher')]

        switchers.forEach(item => {
            item.addEventListener('click', function() {
                switchers.forEach(item => item.parentElement.classList.remove('is-active'))
                this.parentElement.classList.add('is-active')
            })
        })

    </script>


@endsection
