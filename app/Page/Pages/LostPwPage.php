<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;
use Vici\Model\User\UserRepository;
use Vici\Mail\Mailer;
use Vici\Security\Turnstile;

class LostPwPage extends PageRenderer
{
    private string $template = 'lostpw.tpl';

    public function __construct(Session $session)
    {
        $email = trim((string)($_POST['frm_email'] ?? ''));
        $error = '';
        $done = false;

        $turnstile = new Turnstile($_ENV['TURNSTILE_SECRET_KEY'] ?? '', $_ENV['TURNSTILE_SITE_KEY'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $email !== '') {
            $token = $_POST['cf-turnstile-response'] ?? '';
            if ($session->isVerifiedRealUser() || $turnstile->validate($token, $_SERVER['REMOTE_ADDR'] ?? '')) {
                $repo = new UserRepository($session->getDBConnector());
                $user = $repo->findByEmail(strtolower($email));
                if ($user !== null) {
                    $code = $repo->createPasswordResetCode($user->getId());
                    (new Mailer($session))->sendPasswordReset($email, $user->getRealName(), $code);
                }
                // deliberately neutral: don't reveal whether the address is registered
                $done = true;
            } else {
                $error = $session->translator->get("Could not identify you as a human.");
            }
            usleep(500000);
        }

        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);
        $this->assign('lostpw_email_previous', $email);
        $this->assign('lostpw_done', $done);
        $this->assign('error_message', $error);
        $this->assign('is_verified_real_user', $session->isVerifiedRealUser());
        $this->assign('turnstile_sitekey', $turnstile->getSiteKey());
    }
}
