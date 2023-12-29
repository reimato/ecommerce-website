<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'components/connect.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

session_start();

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    $user_id = '';
}

function generateOTP()
{
    return rand(100000, 999999); // Generate a random 6-digit OTP
}

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);
    $pass = sha1($_POST['pass']);
    $pass = filter_var($pass, FILTER_SANITIZE_STRING);
    $cpass = sha1($_POST['cpass']);
    $cpass = filter_var($cpass, FILTER_SANITIZE_STRING);

    $otp = generateOTP(); // Generate OTP

    $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
    $select_user->execute([$email,]);
    $row = $select_user->fetch(PDO::FETCH_ASSOC);

    if ($select_user->rowCount() > 0) {
        $message[] = 'Email already exists!';
    } else {
        if ($pass != $cpass) {
            $message[] = 'Confirm password not matched!';
        } else {
            $insert_user = $conn->prepare("INSERT INTO `users`(name, email, password, otp) VALUES(?,?,?,?)");
            $insert_user->execute([$name, $email, $cpass, $otp]);

            // Send OTP to the user's email
            $mail = new PHPMailer(true);

            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'your@gmail.com'; // Replace with your Gmail address
                $mail->Password   = 'your_password'; // Replace with your Gmail password
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;

                //Recipients
                $mail->setFrom('your@gmail.com', 'Your Name'); // Replace with your name and Gmail address
                $mail->addAddress($email, $name); // Recipient email and name

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Your OTP for registration';
                $mail->Body    = 'Your OTP is: ' . $otp;

                $mail->send();
                $message[] = 'OTP sent to your email. Please verify!';
            } catch (Exception $e) {
                $message[] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
}
?>

<!-- The rest of your HTML code remains the same -->
