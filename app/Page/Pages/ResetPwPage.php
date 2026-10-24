<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;
use Vici\Model\User\UserRepository;

class ResetPwPage extends PageRenderer
{
    private string $template = 'reset.tpl';

    public function __construct(Session $session)
    {
        $code = trim((string)($_GET['code'] ?? ''));
        $error = '';
        $done = false;
        $user = null;

        if ($code === '') {
            $error = $session->translator->get("No confirmation code supplied.");
        } else {
            $repo = new UserRepository($session->getDBConnector());
            $user = $repo->findAccountByResetCode($code);

            if ($user === null) {
                $error = $session->translator->get("Confirmation code not found");
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = (string)($_POST['password'] ?? '');
                $passwordRepeat = (string)($_POST['passwordrepeat'] ?? '');

                if (mb_strlen($password) < 8) {
                    $error = $session->translator->get("Password must be at least 8 characters.");
                } elseif ($password !== $passwordRepeat) {
                    $error = $session->translator->get("Passwords do not match.");
                } else {
                    $repo->resetPassword($user->getId(), $password);
                    $done = true;
                }
            }
        }

        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);
        $this->assign('reset_done', $done);
        $this->assign('reset_error', $error);
        $this->assign('reset_code', $code);
        $this->assign('reset_account_name', $user !== null ? $user->getName() : '');
    }
}
