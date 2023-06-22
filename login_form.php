<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>login form</title>

   <!-- custom css file link  -->

   <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css"
      integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <div class="form-container">

      <form action="" method="post" id = "login-form">
         <h3>login now</h3>
         <span class="error-msg" style="display: none;"></span>

         <input type="email" name="email" placeholder="enter your email" id="email" required>
         <input type="password" name="password"  placeholder="enter your password" id="password" required>
         <input type="submit" name="submit" value="login now" class="form-btn" id="submit">

         <p>don't have an account? <a href="register_form.php">register now</a></p>
      </form>

   </div>

   <script type="text/javascript">

      $(document).ready(function () {

         $('form').on("click" , "#submit" , function (event) {

            event.preventDefault()

            var email = $("#email").val();
            var password = $("#password").val();

            if (!validateEmail(email)) {
               $('.error-msg').text('Invalid email format!');
               $('.error-msg').show();
               return;
            }
            
            if (email.length !== 0 && password.length !== 0 ) {


               $.ajax({
               url: "process.php",
               type: "POST",
               data: { email: email, password: password ,action: 'login_data' },
               success: function (response) {
                  if (response.success) {
                     if (response.user_type === 'admin') {
                        window.location.href = 'admin_page.php';
                     } else if (response.user_type === 'user') {
                        window.location.href = 'user_page.php';
                     }
                  } else {
                     $('.error-msg').text(response.error);
                     $('.error-msg').show();
                  }
               }
            });
               
            }else{
               alert("Empty Fields");
            }
         });

         function validateEmail(email) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
         }
      });

   </script>


</body>

</html>