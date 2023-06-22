<?php

require 'config.php';

session_start();

if(!isset($_SESSION['admin_name'])){
   header('location:login_form.php');
}
$users = []; 

$query = "SELECT id, name FROM users WHERE user_type = 'user'";
$result = mysqli_query($conn, $query);
while ($row = mysqli_fetch_assoc($result)) {
   $users[] = $row;
}
// print_r($users); die;
?>

<!DOCTYPE html>
<html lang="en">

<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>admin page</title>


   <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css"
      integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">



   <link rel="stylesheet" href="css/style.css">

</head>

<body>

   <div class="container">

      <div class="content">
         <h3>hi, <span>admin</span></h3>
         <h1>welcome <span>
               <?php echo $_SESSION['admin_name'] ?>
            </span></h1>
         <p>this is an admin page</p>

         <form id="form">
            <div class="form-row align-items-center">
               <div class="col-auto">
      
                  <input type="text" class="form-control mb-2" id="domain" placeholder="Domain Name" required>
               </div>
               <div class="col-auto">
                  <input type="date" class="form-control mb-2" id="expiryDate" placeholder="" required>
               </div>
               <div class="col-auto">
                  <select class="form-control mb-2" id="selectUser" placeholder="User Name">
                     <option disabled selected> Choose User Name</option>
                     <?php
            
                        foreach ($users as $user) {
                           echo "<option value=\"" . $user['id'] . "\">" . $user['name'] . "</option>";
                        }  
                     ?>
                  </select>
               </div>
               <div class="col-auto">
                  <button type="submit" class="btn btn-primary mb-2" id="submit">Submit</button>
               </div>
            </div>
         </form>

         <a href="logout.php" class="btn" style="position: absolute; top: 10px; right: 10px;">logout</a>

         <div id="tableData">

         </div>

      </div>

      <!-- Modal -->

      <div class="modal1" style="display: none;">
         <div class="modal-dialog" role="document">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Edit Domain</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                     <span aria-hidden="true">&times;</span>
                  </button>
               </div>
               <div class="modal-body">

               </div>
            </div>
         </div>
      </div>

   </div>

   <script type="text/javascript">

      $(document).ready(function () {

         loadTable();
         var validExtensions = ['com', 'org', 'net', 'edu', 'gov', 'mil', 'int', 'biz', 'info', 'name', 'pro', 'coop', 'aero', 'museum', 'travel', 'jobs', 'mobi', 'cat', 'post', 'tel', 'in', 'co.in'];

         $('form').submit(function (event) {

            event.preventDefault();
            var domain = $('#domain').val().toLowerCase();
            var domainWithoutWWW = domain.replace(/^www\./i, '');
            var extension = getExtension(domainWithoutWWW);
            var selectedUser = $('#selectUser').val();
            var expiryDate = $('#expiryDate').val();

            if (validExtensions.indexOf(extension) === -1) {
               event.preventDefault();
               alert('Invalid domain extension!');
            }

            if (selectedUser === null) {
               event.preventDefault();
               alert('Please select a user!');
            }

            if (expiryDate === "") {
               event.preventDefault();
               alert('Please enter the expiry date!');
            }

            if (domainWithoutWWW.length !== 0 && selectedUser.length !== 0 && expiryDate.length !== 0) {

               $.ajax({
                  type: 'POST',
                  url: 'process.php',
                  data: {
                     action: 'saveDomainData',
                     domain: domainWithoutWWW,
                     expiryDate: expiryDate,
                     user_id: selectedUser
                  },
                  success: function (response) {

                     if (response == 1) {
                        $("#form").trigger("reset");
                        alert("Domain data saved successfully.");
                        loadTable();
                     } else {
                        $("#form").trigger("reset");
                        alert(response);
                     }
                  }
               });
            }
         });

         function loadTable() {

            $.ajax({
               url: "process.php",
               type: "POST",
               data: { action: 'load_user_data' },
               success: function (data) {
                  $("#tableData").html(data);
               }
            });
         }

         // open and load data of edit modal
         $(document).on("click", ".editBtn", function () {

            $('.modal1').show();
            $('.content').hide();
            var kid = $(this).data("eid");

            $.ajax({
               url: "process.php",
               type: "POST",
               data: { user_id: kid, action: 'load_edit_data' },
               success: function (data) {
                  $(".modal-body").html(data);
               }
            });
         });

         //delete Record

         $(document).on("click", ".deleteBtn", function () {

            var kid = $(this).data("eid");

            $.ajax({
               url: "process.php",
               type: "POST",
               data: { user_id: kid, action: 'delete_record' },
               success: function (response) {
                  
                  if (response == 1) {
                     alert("Data Deleted successfully.");
                     loadTable();
                  }else{
                     alert(response);
                  }
               }
            });
         });

         // save editForm

         $(document).on("click", "#editsubmit", function (event) {

            event.preventDefault();

            var domain = $('#editdomain').val().toLowerCase();
            var domainWithoutWWW = domain.replace(/^www\./i, '');
            var extension = getExtension(domainWithoutWWW);
            var selectedUser = $('#editselectUser').val();
            var expiryDate = $('#editexpiryDate').val();
            var id = $('#edit-id').val();

            if (validExtensions.indexOf(extension) === -1) {
               event.preventDefault();
               alert('Invalid domain extension!');
            }

            if (expiryDate === "") {
               event.preventDefault();
               alert('Please enter the expiry date!');
            }

            if (domainWithoutWWW.length !== 0 && id.length !== 0 && expiryDate.length !== 0) {

               $.ajax({
                  type: 'POST',
                  url: 'process.php',
                  data: {
                     action: 'saveEditDomainData',
                     user_id: id,
                     domain: domainWithoutWWW,
                     expiryDate: expiryDate,
                  },
                  success: function (response) {

                     if (response == 1) {
                        $("#editform").trigger("reset");
                        alert("Domain data Updated successfully.");
                        $('.modal1').hide();
                        $('.content').show();
                        loadTable();
                     } else {
                        alert(response);
                     }
                  }
               });
            }
         });

         $(document).on("click", ".close", function () {
            $(".modal1").hide();
            $(".content").show();
            loadTable();
         });

      });


      function getExtension(domain) {
         var parts = domain.split('.');
         var lastPart = parts[parts.length - 1];
         return lastPart;
      }
   </script>

</body>

</html>