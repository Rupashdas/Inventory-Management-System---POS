<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6 center-screen">
            <div class="card animated fadeIn w-90  p-4">
                <div class="card-body">
                    <h4>EMAIL ADDRESS</h4>
                    <br/>
                    <form onsubmit="VerifyEmail(event)">
                        <label>Your email address</label>
                        <input id="email" placeholder="User Email" class="form-control" type="email"/>
                        <br/>
                        <button type="submit" class="btn w-100 float-end bg-gradient-primary">Next</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
   async function VerifyEmail(event) {
        event.preventDefault();

        let email = document.getElementById('email').value;
        if(email.length === 0){
           errorToast('Please enter your email address')
           return;
        }
        showLoader();
        try{
            let res = await axios.post('/send-otp', {email: email});
            hideLoader();
            if(res.status===200 && res.data['status']==='success'){
                successToast(res.data['message'])
                sessionStorage.setItem('email', email);
                setTimeout(function (){
                    window.location.href = '/verifyOtp';
                }, 1000)
            }
            else{
                errorToast(res.data['message'])
            }
        }catch(error){
            if(error.response){
                errorToast(error.response.data.message);
            }else{
                errorToast('Something went wrong');
            }
            hideLoader();
        }

    }
</script>
