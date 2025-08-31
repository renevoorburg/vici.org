<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;
use Vici\Model\User\UserRepository;
use Vici\Security\Turnstile;

class LoginPage extends PageRenderer
{

    private Session $session;
    private string $template = 'login.tpl';
    private string $message = '';
    private string $error_message = '';

    public function __construct(Session $session)
    {
        $this->session = $session;

        $accountname = $_POST['accountname'] ?? null;
        $password = $_POST['password'] ?? null;
        $token = $_POST['cf-turnstile-response'] ?? '';

        $turnstile = new Turnstile($_ENV['TURNSTILE_SECRET_KEY'], $_ENV['TURNSTILE_SITE_KEY']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            if ($this->session->isVerifiedRealUser() || $turnstile->validate($token, $_SERVER['REMOTE_ADDR'] ?? '')) {
                if ($accountname && $password) {
                    $userRepository = new UserRepository($this->session->getDBConnector());
                    $user = $userRepository->authenticateUser($accountname, $password);
        
                    if ($user) {    
                        $this->session->setIsVerifiedRealUser(true);
                        $this->session->setUser($user);
                        header("Location: /");
                        exit;
                    } else {
                        $this->error_message = $this->session->translator->get("Invalid username or password.");
                    }
                }
            } else {
                $this->session->setIsVerifiedRealUser(false);
                $this->error_message = $this->session->translator->get("Could not identify you as a human.");
            }
            usleep(500000); 
        }

        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);
        $this->assign('form_accountname_previous', $accountname ?? '');
        $this->assign('message', $this->getMessage());
        $this->assign('error_message', $this->error_message);
        $this->assign('is_verified_real_user', $this->session->isVerifiedRealUser());
        $this->assign('turnstile_sitekey', $turnstile->getSiteKey());  
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