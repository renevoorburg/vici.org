<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;
use Vici\Model\Users\UserRepository;

class LoginPage extends PageRenderer
{

    private Session $session;
    private string $template = 'login.tpl';
    private string $message = '';
    private string $error_message = '';

    public function __construct(Session $session)
    {
        $this->session = $session;

        $accountname = isset($_POST['accountname']) ? (string)$_POST['accountname'] : null;
        $password = isset($_POST['password']) ? trim($_POST['password']) : null;

        $secretKey = $_ENV['TURNSTILE_SECRET_KEY'];
        $token = $_POST['cf-turnstile-response'] ?? '';

        $verify = file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'secret'   => $secretKey,
                    'response' => $token,
                    'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
                ]),
            ],
        ]));

        $responseData = json_decode($verify, true);

        if ($responseData['success']) {
            if ($accountname && $password) {
                $userRepository = new UserRepository($this->session->getDBConnector());
                $user = $userRepository->authenticateUser($accountname, $password);
    
                if ($user) {
                    $this->session->setUser($user);
                    // echo "User authenticated: " . $user->getName();
                    header("Location: /");
                    exit;
                } else {
                    $this->error_message = $this->session->translator->get("Invalid username or password.");
                }
            }
        } elseif ($token) {
            $this->error_message = $this->session->translator->get("Could not identify you as a human.");
        }

        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);
        $this->assign('message', $this->getMessage());
        $this->assign('error_message', $this->error_message);
        $this->assign('turnstile_sitekey', $_ENV['TURNSTILE_SITE_KEY']);  
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