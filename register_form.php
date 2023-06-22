<!-- <?php

require 'config.php';

if(isset($_POST['submit'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $pass = md5($_POST['password']);
   $cpass = md5($_POST['cpassword']);
   $user_type = $_POST['user_type'];

   $select = " SELECT * FROM users WHERE email = '$email' ";

   $result = mysqli_query($conn, $select);

   if(mysqli_num_rows($result) > 0){

      $error[] = 'user already exist!';

   }else{

      if($pass != $cpass){
         $error[] = 'password not matched!';
      }else{
         $insert = "INSERT INTO users(name, email, password, user_type) VALUES('$name','$email','$pass','$user_type')";
         mysqli_query($conn, $insert);
         header('location:login_form.php');
      }
   }

};


?> -->

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>register form</title>

   <!-- custom css file link  -->
   <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css"
      integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
      
   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <div class="form-container">

      <form id="register-form">
         <h3>Register Now</h3>
         <span class="error-msg" style="display: none;"></span>
         <input type="text" name="name" required placeholder="Enter your name" id="name">
         <input type="email" name="email" required placeholder="Enter your email" id="email">
         <input type="password" name="password" required placeholder="Enter your password" id="password">
         <input type="password" name="cpassword" required placeholder="Confirm your password" id="cpassword">
         <select name="user_type" id="user_type" hidden>
            <option value="user">User</option>
         </select>
         <input type="submit" name="submit" value="Register Now" class="form-btn">
         <p>Already have an account? <a href="login_form.php">Login Now</a></p>
      </form>

   </div>

   <script type="text/javascript">

      $(document).ready(function () {

         $('#register-form').on('submit', function (e) {
            e.preventDefault();

            var name = $('#name').val();
            var email = $('#email').val();
            var password = $('#password').val();
            var cpassword = $('#cpassword').val();
            var user_type = $('#user_type').val();

            if (!validateEmail(email)) {
               $('.error-msg').text('Invalid email format!');
               $('.error-msg').show();
               return;
            }

            $.ajax({
               type: 'POST',
               url: 'process.php',
               data: {
                  name: name,
                  email: email,
                  password: password,
                  cpassword: cpassword,
                  user_type: user_type,
                  action : 'registration'
               },
               success: function (response) {
                  if (response.success) {
                     window.location.href = 'login_form.php';
                  } else {
                     $('.error-msg').text(response.error);
                     $('.error-msg').show();
                  }
               }
            });
         });

         function validateEmail(email) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
         }
      });
   </script>
</body>

</html>