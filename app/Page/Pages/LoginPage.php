<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;
use Vici\Model\Users\UserRepository;

class LoginPage extends PageRenderer
{

    private Session $session;
    private string $template = 'login.tpl';

    public function __construct(Session $session)
    {
        $this->session = $session;

        $accountname = isset($_POST['accountname']) ? (string)$_POST['accountname'] : null;
        $password = isset($_POST['password']) ? trim($_POST['password']) : null;

        if ($accountname && $password) {
            $userRepository = new UserRepository($this->session->getDBConnector());
            $user = $userRepository->authenticateUser($accountname, $password);

            if ($user) {
                $this->session->setUser($user);
                // echo "User authenticated: " . $user->getName();
                header("Location: /");
                exit;
            }
        }


        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);
        $this->assign('message', $this->getMessage());
    }

    private function getMessage() : string
    {
        $msg = "";
        if (isset($_GET['wait'])) {
            $sec = (int)$_GET['wait'];
            $msg = "<p>".sprintf($this->session->translator->get("ERROR: Page limit for anonymous users reached. Log in or wait %s seconds."), $sec)."</p>";
        }
        if (isset($_GET['loginrequired'])) {
            $msg = "<p>" . sprintf($this->session->translator->get("ERROR: You need to log in to access this dataservice."), $sec) . "</p>";
        }
        return $msg;
    }


}