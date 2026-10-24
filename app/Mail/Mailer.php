<?php

namespace Vici\Mail;

use Vici\Session\Session;

class Mailer
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function sendActivation(string $to, string $realName, string $code): bool
    {
        $link = $this->session->getViciBase() . '/confirm?code=' . $code;
        $body = sprintf(
            $this->session->translator->get("Dear %s,\n\nYour account at Vici.org needs to be activated. This is done by following the next link:\n\n%s\n\nVici.org - Roman history nearby -\n"),
            $realName,
            $link
        );
        $subject = $this->session->translator->get("Activate your account at Vici.org");
        return $this->send($to, $subject, $body);
    }

    public function sendPasswordReset(string $to, string $realName, string $code): bool
    {
        $link = $this->session->getViciBase() . '/reset?code=' . $code;
        $body = sprintf(
            $this->session->translator->get("Dear %s,\n\nSomebody, likely you, has requested to reset the password for the Vici.org account associated with this email address. This can be done by following this link:\n\n%s\n\nYou may ignore this message if you don't want to reset the password.\n\nVici.org - Roman history nearby -\n"),
            $realName,
            $link
        );
        $subject = $this->session->translator->get("Reset your password at Vici.org");
        return $this->send($to, $subject, $body);
    }

    public function send(string $to, string $subject, string $body): bool
    {
        $from = $_ENV['MAIL_FROM'] ?? 'noreply@vici.org';
        $headers = "From: Vici <$from>\r\n"
            . "Reply-To: $from\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "MIME-Version: 1.0\r\n";

        $ok = @mail($to, $subject, wordwrap($body, 70), $headers, '-f ' . $from);
        if (!$ok) {
            error_log("Mailer: could not send mail to {$to} (is an MTA/sendmail configured?)");
        }
        return $ok;
    }
}
