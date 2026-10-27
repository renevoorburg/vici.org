<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;
use Vici\Model\User\UserRepository;

class ConfirmPage extends PageRenderer
{
    private string $template = 'confirm.tpl';

    public function __construct(Session $session)
    {
        $code = trim((string)($_GET['code'] ?? ''));

        $message = '';
        $error = '';

        if ($code === '') {
            $error = $session->translator->get("No confirmation code supplied.");
        } else {
            $repo = new UserRepository($session->getDBConnector());
            $accountId = $repo->findAccountIdByConfirmationCode($code);

            if ($accountId === null) {
                $error = $session->translator->get("Confirmation code not found");
            } elseif ($repo->activateAccount($accountId)) {
                $message = $session->translator->get("Your account has been activated.");
            } else {
                $error = $session->translator->get("Account already activated.");
            }
        }

        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);
        $this->assign('confirm_message', $message);
        $this->assign('confirm_error', $error);
    }
}
