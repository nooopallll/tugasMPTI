<?php 
// LANGKAH 1: SEMUA LOGIKA PHP DIPINDAHKAN KE ATAS
session_start();
include('./db_connect.php'); 

// Redirect jika sudah login
if(isset($_SESSION['login_id'])){
    header("location:index.php?page=home");
    exit(); // Penting: Hentikan eksekusi script
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Admin | Laundry Management System</title>
    
<?php 
// Include header setelah logika session
include('./header.php'); 
?>

</head>
<style>
    body{
        width: 100%;
        height: calc(100%);
    }
    main#main{
        width:100%;
        height: calc(100%);
        background:white;
    }
    #login-right{
        position: absolute;
        right:0;
        width:40%;
        height: calc(100%);
        background:white;
        display: flex;
        align-items: center;
    }
    #login-left{
        position: absolute;
        left:0;
        width:60%;
        height: calc(100%);
        background:#59b6ec61;
        display: flex;
        align-items: center;
    }
    #login-right .card{
        margin: auto
    }
    .logo {
    margin: auto;
    font-size: 8rem;
    background: white;
    padding: .5em 0.7em;
    border-radius: 50% 50%;
    color: #000000b3;
}

</style>

<body>

  <main id="main" class=" bg-dark">
        <div id="login-left">
            <div class="logo">
                <div class="laundry-logo"></div>
            </div>
        </div>
        <div id="login-right">
            <div class="card col-md-8">
                <div class="card-body">
                    <form id="login-form" >
                        <div class="form-group">
                            <label for="username" class="control-label">Username</label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="password" class="control-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <center>
                            <button type="submit" class="btn-sm btn-block btn-wave col-md-4 btn-primary">Login</button>
                        </center>
                    </form>
                </div>
            </div>
        </div>
   

  </main>

  <a href="#" class="back-to-top"><i class="icofont-simple-up"></i></a>

</body>
<script>
    $('#login-form').submit(function(e){
        e.preventDefault()
        // LANGKAH 2.B: Selector diubah ke button[type="submit"]
        $('#login-form button[type="submit"]').attr('disabled',true).html('Logging in...');
        if($(this).find('.alert-danger').length > 0 )
            $(this).find('.alert-danger').remove();
        $.ajax({
            url:'ajax.php?action=login',
            method:'POST',
            data:$(this).serialize(),
            error:err=>{
                console.log(err)
                // Selector diubah ke button[type="submit"]
                $('#login-form button[type="submit"]').removeAttr('disabled').html('Login');
            },
            success:function(resp){
                if(resp == 1){
                    location.href ='index.php?page=home';
                }else if(resp == 2){
                    location.href ='voting.php';
                }else{
                    $('#login-form').prepend('<div class="alert alert-danger">Username or password is incorrect.</div>')
                    // Selector diubah ke button[type="submit"]
                    $('#login-form button[type="submit"]').removeAttr('disabled').html('Login');
                }
            }
        })
    })
</script>   
</html>