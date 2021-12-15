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

                <form class="form form-login">

                    <fieldset>
                        <div class="input-block">
                            <label for="Fname">Full Name</label>
                            <input id="Name" type="text" required placeholder="Full Name.." >
                        </div>
                        <div class="input-block">
                            <label for="ID">ID Number</label>
                            <input type="text" id="idnum" name="idnum" placeholder="ID Number..">
                        </div>
                        <div class="input-block">
                            <label for="Email">Email</label>
                            <input type="text" id="Email" name="email" placeholder="Email..">
                        </div>
                        <div class="input-block">
                            <label for="Phonenum">Phone Number</label>
                            <input type="text" id="Phonenum" name="Phonenum" placeholder="Phone Number..">
                        </div>

                        <div class="input-block">
                            <label for="Gender">Gender</label>
                            <select style="width: 100%; background-color: #eef9fe; font-size: 16px;" id="Gender" name="Gender">
                                <option value="Select">--Select--</option>
                                <option value="Female">Female</option>
                                <option value="Male">Male</option>
                            </select>
                        </div>

                        <div class="input-block">
                            <label for="date">Date Of Birth</label>
                            <input type="date" id="date" name="date" placeholder="Date Of Birth..">
                        </div>

                        <div class="input-block">
                            <label for="blood">Blood Group:</label>
                            <select style="width: 100%; background-color: #eef9fe; font-size: 16px;" name="Blood" id="Blood" >
                                <option value="Select">--Select Your Blood Group--</option>
                                <option value="A+">A+</option>
                                <option value="AB+">AB+</option>
                                <option value="A-">A-</option>
                                <option value="AB-">AB-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>

                        <div class="input-block">
                            <label for="lastdate">Last Donation Date</label>
                            <input type="date" id="lastdate" name="lastdate" placeholder="Last Donation Date..">
                        </div>


                        <div class="input-block">
                            <label for="Diseases">Suffering From Any Diseases?..</label> <br>
                            <textarea style="width: 100%; background-color: #eef9fe; font-size: 16px;" placeholder="Write Them Here Please....." rows="6" ></textarea>
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

                <form class="form form-signup">

                    <fieldset>


                        <div class="input-block">
                            <label for="Fname">Full Name</label>
                            <input id="Name" type="text" required placeholder="Full Name.." >
                        </div>
                        <div class="input-block">
                            <label for="ID">ID Number</label>
                            <input type="text" id="idnum" name="idnum" placeholder="ID Number..">
                        </div>
                        <div class="input-block">
                            <label for="Phonenum">Phone Number</label>
                            <input type="text" id="Phonenum" name="Phonenum" placeholder="Phone Number..">
                        </div>

                        <div class="input-block">
                            <label for="Gender">Gender</label>
                            <select style="width: 100%; background-color: #eef9fe; font-size: 16px;" id="Gender" name="Gender">
                                <option value="Select">--Select--</option>
                                <option value="Female">Female</option>
                                <option value="Male">Male</option>
                            </select>
                        </div>

                        <div class="input-block">
                            <label for="date">Date Of Birth</label>
                            <input type="date" id="date" name="date" placeholder="Date Of Birth..">
                        </div>

                        <div class="input-block">
                            <label for="blood">Blood Group:</label>
                            <select style="width: 100%; background-color: #eef9fe; font-size: 16px;" name="Blood" id="Blood" >
                                <option value="Select">--Select Your Blood Group--</option>
                                <option value="A+">A+</option>
                                <option value="AB+">AB+</option>
                                <option value="A-">A-</option>
                                <option value="AB-">AB-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>

                        <div class="input-block">
                            <label for="Country">Country:</label>
                            <select style="width: 100%; background-color: #eef9fe; font-size: 16px;" style="width: 180px;" name="Country" id="Country" >
                                <option value="Select">--Your Country--</option>
                                <option value="Male">Jordan</option>
                            </select>
                        </div>

                        <div class="input-block">
                            <label for="City">City:</label>
                            <select style="width: 100%; background-color: #eef9fe; font-size: 16px;" style="width: 180px;" name="City" id="City" >
                                <option value="Select">--Your City--</option>
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
                        </div>

                        <div class="input-block">
                            <label for="address">Detailed Address..</label> <br>
                            <textarea style="width: 100%; background-color: #eef9fe; font-size: 16px;" placeholder="Write The Address Here Please....." rows="6" style="width: 520px;"></textarea>
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
