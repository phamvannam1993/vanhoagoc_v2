<?php

namespace App\Helpers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Jobs\SendMailJob;

class SendMailHelper
{
    public static function send($to, $subject, $body, $from = null, $fromName = null)
    {
        try {
            // Đưa vào queue
            SendMailJob::dispatch($to, $subject, $body, $from, $fromName);
        } catch (\Throwable $e) {
            return false;
        }
        return true;
    }

    public static function sendNow($to, $subject, $body, $from = null, $fromName = null)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = env('MAIL_USERNAME');
            $mail->Password   = env('MAIL_PASSWORD');
            $mail->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');
            $mail->Port       = env('MAIL_PORT', 587);
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($from ?? env('MAIL_FROM_ADDRESS'), $fromName ?? env('MAIL_FROM_NAME'));
            $mail->addAddress($to);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            \Log::error('PHPMailer Error: ' . $mail->ErrorInfo);
            return false;
        }
    }
}
