<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;
use Vici\Model\User\UserRepository;

class SettingsPage extends PageRenderer
{
    private string $template = 'settings.tpl';

    public function __construct(Session $session)
    {
        $error = '';
        $user = $session->getUser();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $realName = (string)($_POST['realName'] ?? '');
            $realNameTrim = trim($realName);

            if (mb_strlen(preg_replace('/\s+/', ' ', $realNameTrim)) < 4 || $realNameTrim !== trim($realNameTrim)) {
                $error = $session->translator->get("Display name must be at least 4 characters.");
            } else {
                $realName = preg_replace('/\s+/', ' ', $realNameTrim);
                $repo = new UserRepository($session->getDBConnector());
                $repo->updateRealName($user->getId(), $realName);
                $user->setRealName($realName);
                $session->setUser($user);
                $return = $_GET['return'] ?? '';
                header("Location: " . (preg_match('#^/[^/]#', $return) ? $return : '/'));
                exit;
            }
        }

        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);
        $this->assign('settings_realname', $user->getRealName());
        $this->assign('error_message', $error);
    }
}
