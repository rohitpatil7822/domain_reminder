<?php

require 'config.php';

function registration($conn){

    if (isset($_POST)) {

        $name = mysqli_real_escape_string($conn, $_POST['name']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $pass = md5($_POST['password']);
        $cpass = md5($_POST['cpassword']);
        $user_type = $_POST['user_type'];

        $select = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $select);

        if (mysqli_num_rows($result) > 0) {
            $response = array('success' => false, 'error' => 'User already exists!');
        } else {
            if ($pass != $cpass) {
                $response = array('success' => false, 'error' => 'Passwords do not match!');
            } else {
                $insert = "INSERT INTO users(name, email, password, user_type) VALUES('$name','$email','$pass','$user_type')";
                mysqli_query($conn, $insert);
                $response = array('success' => true);
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }
}

function login_data($conn){

    if (isset($_POST)) {

        // ini_set('session.gc_maxlifetime', 3600);

        session_start();

        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $pass = md5($_POST['password']);

        $select = "SELECT * FROM users WHERE email = '".$email."' AND password = '".$pass."'";
        $result = mysqli_query($conn, $select);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);

            if ($row['user_type'] === 'admin') {
                $_SESSION['admin_name'] = $row['name'];
                $_SESSION['user_id'] = $row['id'];
                $response = array('success' => true, 'user_type' => 'admin');
            } elseif ($row['user_type'] === 'user') {
                $_SESSION['user_name'] = $row['name'];
                $_SESSION['user_id'] = $row['id'];
                $response = array('success' => true, 'user_type' => 'user');
            }
        } else {
            $response = array('success' => false, 'error' => 'Incorrect email or password!');
        }

        header('Content-Type: application/json');
        echo json_encode($response);

    }
}

function saveDomainData($conn){

    if (isset($_POST)) {
        
        $domain = mysqli_real_escape_string($conn,$_POST['domain']); 
        $expiryDate = mysqli_real_escape_string($conn,$_POST['expiryDate']); 
        $user_id = intval($_POST['user_id']); 

        if (!empty($domain) && !empty($expiryDate) && !empty($user_id)) {
            
            $select = "SELECT * FROM domains WHERE domain_name = '".$domain."'";

            $result = mysqli_query($conn, $select);

            if(mysqli_num_rows($result) > 0){

                echo $domain." Already Exists";
            }else {
                
                $insQry = "INSERT INTO domains SET domain_name = '".$domain."' , expiry_date = '".$expiryDate."' , user_id = ".$user_id;

                if (mysqli_query($conn, $insQry)) {
                    echo 1;
                } else {
                    echo "Error: " . mysqli_error($conn);
                }
            }
        }

    }
}

function load_user_data($conn){

   $query = "SELECT u.id , u.name , d.domain_name , d.expiry_date FROM domains d INNER JOIN users u ON d.user_id = u.id WHERE u.user_type != 'admin'";

   $result = mysqli_query($conn, $query);

   $output = "";

   if (mysqli_num_rows($result) > 0) {
      $output .= "<h2>User Details</h2>
      <table class='table table-bordered' style='overflow: auto; max-height: 100px;'>
      <thead>
        <tr>
          <th scope='col'>User Name</th>
          <th scope='col'>Domain Name</th>
          <th scope='col'>Expiry Date</th>
          <th scope='col'>Status</th>
          <th scope='col'>Edit</th>
          <th scope='col'>Delete</th>
        </tr>
      </thead>
      <tbody>";

      while ($row = mysqli_fetch_assoc($result)) {
         $domain = $row['domain_name'];
         $expiryDate = $row['expiry_date'];
         $status = '';
         $user_name = $row['name'];

         $currentDate = date('Y-m-d');
         $diff = strtotime($expiryDate) - strtotime($currentDate);
         $daysUntilExpiry = floor($diff / (60 * 60 * 24));

         if ($daysUntilExpiry < 1) {
            $status = 'Expired';
            $backgroundColor = 'red';
         }elseif ($daysUntilExpiry == 1) {
            $status = 'Expiring Today';
            $backgroundColor = 'red';
         } elseif ($daysUntilExpiry <= 3) {
            $status = 'Expiring In '.$daysUntilExpiry.' days';
            $backgroundColor = 'orange';
         } else {
            $status = 'Active';
            $backgroundColor = 'white';
         }

         $output .= "<tr>
          <td>$user_name</td>
          <td style='background-color: $backgroundColor;'>$domain</td>
          <td>$expiryDate</td>
          <td>$status</td>
          <td align='center'><button class='btn editBtn btn-primary' id='editBtns' data-eid='{$row['id']}' style='padding: 5px 10px; font-size: 12px;'>Edit</button></td>
          <td align='center'><button class='btn deleteBtn btn-primary' id='deleteBtn' data-eid='{$row['id']}' style='padding: 5px 10px; font-size: 12px;'>Delete</button></td>
        </tr>";
      }

      $output .= "</tbody></table>";
   } else {
      $output = "<p>No domains found.</p>";
   }
   echo $output;
}

function load_edit_data($conn){

    if (isset($_POST)) {
        
        $user_id = intval($_POST['user_id']); 

        if (!empty($user_id)) {
            
            $select = "SELECT u.name , d.domain_name , d.expiry_date FROM domains d INNER JOIN users u ON d.user_id = u.id WHERE u.id = ".$user_id;

            $result = mysqli_query($conn, $select);

            $output = "";
            if(mysqli_num_rows($result) > 0){

                $rows = mysqli_fetch_assoc($result);

                $domain = $rows['domain_name'];
                $expiryDate = $rows['expiry_date'];
                $user_name = $rows['name'];
                
                $output = "<form id='editform'>
                <div class='form-row align-items-center'>
                   <div class='col-auto'>
                      <input type='text' class='form-control mb-2' id='editdomain' placeholder='Domain Name' value = '{$domain}' required>
                   </div>
                   <div class='col-auto'>
                      <input type='date' class='form-control mb-2' id='editexpiryDate' value = '{$expiryDate}' required>
                   </div>
                   <div class='col-auto'>
                      <select class='form-control mb-2' id='editselectUser' placeholder='User Name'>
                         <option disabled selected>{$user_name}</option>
                      </select>
                   </div>
                   <div class='col-auto'>
                        <input type='text' class='form-control' name='keyword' id='edit-id' value='{$user_id}' hidden>
                   </div>
                   <div class='col-auto'>
                      <button type='submit' class='btn btn-primary mb-2' id='editsubmit'>Submit</button>
                   </div>
                </div>
             </form>";
            }

            echo $output;
        }

    }
}

function saveEditDomainData($conn){

    if (isset($_POST)) {
        
        $domain = mysqli_real_escape_string($conn,$_POST['domain']); 
        $expiryDate = mysqli_real_escape_string($conn,$_POST['expiryDate']); 
        $user_id = intval($_POST['user_id']); 

        if (!empty($domain) && !empty($expiryDate) && !empty($user_id)) {
            
            $select = "SELECT * FROM domains WHERE domain_name = '".$domain."' and expiry_date = '".$expiryDate."'";

            $result = mysqli_query($conn, $select);

            if(mysqli_num_rows($result) > 0){

                echo $domain." Already Exists";
            }else {
                
                $updQry = "UPDATE domains SET domain_name = '".$domain."' , expiry_date = '".$expiryDate."' WHERE user_id = ".$user_id;

                if (mysqli_query($conn, $updQry)) {
                    echo 1;
                } else {
                    echo "Error: " . mysqli_error($conn);
                }
            }
        }

    }

}

function delete_record($conn){

    if (isset($_POST)) {

        $user_id = intval($_POST['user_id']);

        $select = "SELECT d.* FROM domains d INNER JOIN users u ON d.user_id = u.id WHERE u.id = ".$user_id;

        $result = mysqli_query($conn, $select);

        if(mysqli_num_rows($result) > 0){

            // Delete data from the domain table
            $deleteDomainQuery = "DELETE FROM domains WHERE user_id = ".$user_id;
            $ress2 = mysqli_query($conn, $deleteDomainQuery);

            // Delete data from the users table
            $deleteUserQuery = "DELETE FROM users WHERE id = ".$user_id;
            $ress1 =  mysqli_query($conn, $deleteUserQuery);

            if ($ress1 && $ress2) {
                
                echo 1;
            }else {
                echo "Deletion Failed";
            }
        }


    }
}

if (isset($_POST['action'])) {
    $action = $_POST['action'];
 
    switch ($action) {

        case 'registration':
            registration($conn);
        break;
        case 'login_data':
            login_data($conn);
        break;
        case 'saveDomainData':
          saveDomainData($conn);
        break;
        case 'load_user_data':
            load_user_data($conn);
        break;
        case 'load_edit_data':
            load_edit_data($conn);
        break;
        case 'saveEditDomainData':
            saveEditDomainData($conn);
        break;
        case 'delete_record':
            delete_record($conn);
        break;

    }
 }
?>