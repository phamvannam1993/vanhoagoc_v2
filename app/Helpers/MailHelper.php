<?php

namespace App\Helpers;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class MailHelper
{
    public static function send($emailData)
    {
        $mailHost = config('common.mail_service.host');
        $mailPort = config('common.mail_service.port');
        $mailUserName = config('common.mail_service.username');
        $mailPassword = config('common.mail_service.password');
        $dsn = "smtp://" . $mailUserName . ':' . $mailPassword . '@' . $mailHost . ':' . $mailPort;
       
        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);

        $email = (new Email())->from('dai@gotechjsc.com')
            ->to(...$emailData['to'])
            ->subject($emailData['subject'])
            ->html($emailData['html']);

        if (!empty($emailData['cc'])) {
            $email->cc(...$emailData['cc']);
        }

        if (!empty($emailData['bcc'])) {
            $email->cc(...$emailData['bcc']);
        }

        try {
            $mailer->send($email);
            return true;
        } catch (\Exception $e) {
            // log error here
            // $e->getMessage();
        }
    }
}
