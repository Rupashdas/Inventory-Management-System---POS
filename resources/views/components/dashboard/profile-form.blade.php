<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-10">
            <div class="card animated fadeIn w-100 p-3">
                <div class="card-body">
                    <h4>User Profile</h4>
                    <hr/>
                    <form onsubmit="onUpdate(event)">
                        <div class="container-fluid m-0 p-0">
                            <div class="row m-0 p-0">
                                <div class="col-md-4 p-2">
                                    <label>Email Address</label>
                                    <input id="email" placeholder="User Email" class="form-control" type="email" readonly/>
                                </div>
                                <div class="col-md-4 p-2">
                                    <label>First Name</label>
                                    <input id="firstName" placeholder="First Name" class="form-control" type="text"/>
                                </div>
                                <div class="col-md-4 p-2">
                                    <label>Last Name</label>
                                    <input id="lastName" placeholder="Last Name" class="form-control" type="text"/>
                                </div>
                                <div class="col-md-4 p-2">
                                    <label>Mobile Number</label>
                                    <input id="mobile" placeholder="Mobile" class="form-control" type="mobile"/>
                                </div>
                                <div class="col-md-4 p-2">
                                    <label>Password
                                    @if(request()->is('userProfile'))
                                    <p class="small mb-0 d-inline"><em>(Leave blank to keep current password)</em></p>
                                    @endif
                                    </label>
                                    <input id="password" placeholder="User Password" class="form-control" type="password"/>
                                </div>
                            </div>
                            <div class="row m-0 p-0">
                                <div class="col-md-4 p-2">
                                    <button type="submit" class="btn mt-3 w-100  bg-gradient-primary">Update</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    getProfile();
    async function getProfile(){
        showLoader();
        try{
            let res = await axios.get('/user-profile');
            if(res.status === 200 && res.data.status === 'success'){
                const data = res.data.data;
                document.getElementById('email').value = data.email;
                document.getElementById('firstName').value = data.firstName;
                document.getElementById('lastName').value = data.lastName;
                document.getElementById('mobile').value = data.mobile;
                document.getElementById('password').value = data.password;
            }
            hideLoader();
        } catch(err){
            if(err.response){
                errorToast(err.response.data.message || 'Something went wrong');
            } else {
                errorToast('Network error');
            }
            hideLoader();
        }
    }
    async function onUpdate(event){
        event.preventDefault();
        
        let firstName = document.getElementById('firstName').value;
        let lastName = document.getElementById('lastName').value;
        let mobile = document.getElementById('mobile').value;
        let password = document.getElementById('password').value;

        if(firstName.length===0){
            errorToast('First Name is required')
        }
        else if(lastName.length===0){
            errorToast('Last Name is required')
        }
        else if(mobile.length===0){
            errorToast('Mobile is required')
        }
        else if(password.length===0){
            errorToast('Password is required')
        }
        else{
            showLoader();
            let res=await axios.post("/user-update",{
                firstName:firstName,
                lastName:lastName,
                mobile:mobile,
                password:password
            })
            hideLoader();
            if(res.status===200 && res.data['status']==='success'){
                successToast(res.data['message']);
                await getProfile();
            }
            else{
                errorToast(res.data['message'])
            }
        }
    }
</script>
