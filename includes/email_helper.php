<?php

require '../vendor/PHPMailer/src/Exception.php';
require '../vendor/PHPMailer/src/PHPMailer.php';
require '../vendor/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include_once 'email_config.php';

function send_event_invitation($to_email, $to_name, $event_details) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = EMAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = EMAIL_USERNAME;
        $mail->Password   = EMAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = EMAIL_PORT;
        
        $mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
        $mail->addAddress($to_email, $to_name);
        
        $mail->isHTML(true);
        $mail->Subject = 'You are Invited: ' . $event_details['title'];
        
        $mail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif; padding: 20px;'>
            <h2>You are Invited</h2>
            <h3>{$event_details['title']}</h3>
            <p><strong>Description:</strong> {$event_details['description']}</p>
            <p><strong>Location:</strong> {$event_details['location']}</p>
            <p><strong>Date and Time:</strong> {$event_details['start_time']}</p>
            <p><strong>Host:</strong> {$event_details['host_name']}</p>
            <p><a href='{$event_details['rsvp_link']}'>Click here to RSVP</a></p>
            <p>Sent by Eventure Event Planner</p>
        </body>
        </html>
        ";
        
        $mail->AltBody = "You are invited to {$event_details['title']}!\n\n" .
                        "Location: {$event_details['location']}\n" .
                        "Date: {$event_details['start_time']}\n" .
                        "RSVP at: {$event_details['rsvp_link']}";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

function send_rsvp_confirmation($to_email, $to_name, $event_details, $rsvp_status) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = EMAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = EMAIL_USERNAME;
        $mail->Password   = EMAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = EMAIL_PORT;
        
        $mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
        $mail->addAddress($to_email, $to_name);
        
        $mail->isHTML(true);
        $mail->Subject = 'RSVP Confirmed: ' . $event_details['title'];
        
        $mail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif; padding: 20px;'>
            <h2>RSVP Confirmed</h2>
            <p>Hi {$to_name},</p>
            <p>Your RSVP has been recorded as: <strong>" . ucfirst($rsvp_status) . "</strong></p>
            <h3>{$event_details['title']}</h3>
            <p><strong>Location:</strong> {$event_details['location']}</p>
            <p><strong>Date and Time:</strong> {$event_details['start_time']}</p>
            <p>Sent by Eventure Event Planner</p>
        </body>
        </html>
        ";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}

function send_host_rsvp_notification($host_email, $host_name, $guest_name, $event_title, $rsvp_status) {
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = EMAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = EMAIL_USERNAME;
        $mail->Password   = EMAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = EMAIL_PORT;
        
        $mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
        $mail->addAddress($host_email, $host_name);
        
        $mail->isHTML(true);
        $mail->Subject = 'New RSVP for ' . $event_title;
        
        $mail->Body = "
        <html>
        <body style='font-family: Arial, sans-serif; padding: 20px;'>
            <h2>New RSVP</h2>
            <p>Hi {$host_name},</p>
            <p><strong>{$guest_name}</strong> has RSVPed <strong>" . ucfirst($rsvp_status) . "</strong> to your event:</p>
            <h3>{$event_title}</h3>
            <p>Sent by Eventure Event Planner</p>
        </body>
        </html>
        ";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}
?>
