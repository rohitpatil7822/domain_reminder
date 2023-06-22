<?php

require 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';


// Retrieve all domains that are expiring in 3 days or less
$currentDate = date('Y-m-d');
$expiryDateThreshold = date('Y-m-d', strtotime('+3 days'));

$query = "SELECT * FROM domains WHERE expiry_date BETWEEN '$currentDate' AND '$expiryDateThreshold'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
   while ($row = mysqli_fetch_assoc($result)) {
      $domain = $row['domain_name'];
      $expiryDate = $row['expiry_date'];
      $user_id = $row['user_id'];

      $message = "Your domain '$domain' ";
      if ($expiryDate == $currentDate) {
        $message .= "will expire today. Please take immediate action.";
      }else {
        $message .= "is expiring on $expiryDate. Please take necessary actions.";
      }

      sendNotification($user_id, $message);
   }
}

function sendNotification($user_id, $message) {
    global $conn;

    // Get admin email address
    $query = "SELECT name ,email FROM users WHERE user_type = 'admin'";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    $adminEmail = $row['email'];

    // Get user email address
    $user = getUserEmail($user_id);
    $to = $user['email'];
    $subject = "Domain Expiry Notification";
    $headers = "From: $adminEmail";

    $mail = new PHPMailer(true);

    try {
    
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $adminEmail;
        $mail->Password = 'adranzrpjnhcmikj';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587; 
    
        $mail->setFrom($adminEmail, $row['name']);
        $mail->addAddress($to, $user['name']);
    
        $mail->Subject = $subject;
        $mail->Body = $message;
    
        // Send the email
        $mail->send();
        echo 'Email sent successfully!'."<br>";
    } catch (Exception $e) {
        echo 'Error sending email: ' . $mail->ErrorInfo;
    }
    
}

function getUserEmail($user_id) {
    global $conn;
    $query = "SELECT name , email FROM users WHERE id = $user_id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row;
}
?>
