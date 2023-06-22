<?php

require 'config.php';

session_start();

if (!isset($_SESSION['user_name']) && !isset($_SESSION['user_id'])) {
   header('location:login_form.php');
} else {
   
   $query = "SELECT * FROM domains WHERE user_id = ".$_SESSION['user_id'];

   $result = mysqli_query($conn, $query);

   $output = "";

   if (mysqli_num_rows($result) > 0) {
      $output .= "<table class='table table-bordered'>
      <thead>
        <tr>
          <th scope='col'>Domain</th>
          <th scope='col'>Expiry Date</th>
          <th scope='col'>Status</th>
        </tr>
      </thead>
      <tbody>";

      while ($row = mysqli_fetch_assoc($result)) {
         $domain = $row['domain_name'];
         $expiryDate = $row['expiry_date'];
         $status = '';

         $currentDate = date('Y-m-d');
         $diff = strtotime($expiryDate) - strtotime($currentDate);
         $daysUntilExpiry = floor($diff / (60 * 60 * 24));

         if ($daysUntilExpiry < 1) {
            $status = 'Expired';
            $backgroundColor = 'red';
         } elseif ($daysUntilExpiry == 1) {
            $status = 'Expiring In Today';
            $backgroundColor = 'red';
         } elseif ($daysUntilExpiry <= 3) {
            $status = 'Expiring In '.$daysUntilExpiry.' days';
            $backgroundColor = 'orange';
         } else {
            $status = 'Active';
            $backgroundColor = 'white';
         }

         $output .= "<tr>
          <td style='background-color: $backgroundColor;'>$domain</td>
          <td>$expiryDate</td>
          <td>$status</td>
        </tr>";
      }

      $output .= "</tbody></table>";
   } else {
      $output = "<p>No domains found.</p>";
   }
}

?>

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <title>Hello, world!</title>
  </head>
  <body>
    
   <div class="container">
         <div class="content">
            <h1>welcome <span><?php echo $_SESSION['user_name'] ?></span></h1>
            <p>Domain Details</p>
            <div>
               <?php echo $output ?>
            </div>
            <a href="logout.php" class="btn">Logout</a>
         </div>
    </div>


  </body>
</html>
