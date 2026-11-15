<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;
use Vici\Mail\Mailer;
use Vici\Security\Altcha;

class ContactPage extends PageRenderer
{
    private string $template = 'contact.tpl';

    public function __construct(Session $session)
    {
        $error = '';
        $sent = false;
        $email = '';
        $formMessage = '';

        $user = $session->hasUser() ? $session->getUser() : null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formMessage = trim((string)($_POST['message'] ?? ''));
            $email = trim((string)($_POST['email'] ?? ''));
            $replyTo = $user ? $user->getEmail() : $email;

            if (!$session->isCsrfTokenValid($_POST['csrf_token'] ?? null)) {
                $error = $session->translator->get('Invalid form token. Please reload the page and try again.');
            } elseif (mb_strlen($formMessage) < 10) {
                $error = $session->translator->get('Please enter a message of at least 10 characters.');
            } elseif (mb_strlen($formMessage) > 2000) {
                $error = $session->translator->get('Please enter a message of at most 2000 characters.');
            } elseif ($user === null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = $session->translator->get('Please enter a valid e-mail address.');
            } else {
                $verified = $session->isVerifiedRealUser();
                if (!$verified) {
                    $altcha = new Altcha($_ENV['ALTCHA_HMAC_KEY'] ?? '');
                    $verified = $altcha->validate($_POST['altcha'] ?? '');
                }
                if ($verified) {
                    $session->setIsVerifiedRealUser(true);
                    $to = ($_ENV['MAIL_CONTACT'] ?? '') ?: ($_ENV['MAIL_FROM'] ?? 'noreply@vici.org');
                    $body = sprintf(
                            $session->translator->get("Message from %s (%s) via the Vici.org contact form:\n\n"),
                            $user ? $user->getRealName() : $email,
                            $replyTo
                        ) . mb_substr($formMessage, 0, 2000) . "\n";
                    $sent = (new Mailer($session))->send(
                        $to,
                        $session->translator->get('Vici.org: contact form'),
                        $body,
                        $replyTo
                    );
                    if (!$sent) {
                        $error = $session->translator->get('The message could not be sent.');
                    }
                } else {
                    $error = $session->translator->get('Could not identify you as a human.');
                }
            }
            usleep(500000);
        }

        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);
        $this->assign('csrf_token', $session->getCsrfToken());
        $this->assign('is_logged_in', $user !== null);
        $this->assign('form_email', $email);
        $this->assign('form_message', $formMessage);
        $this->assign('error_message', $error);
        $this->assign('sent', $sent);
        $this->assign('is_verified_real_user', $session->isVerifiedRealUser());
        $this->assign('title', $session->translator->get('Contact'));
    }
}
