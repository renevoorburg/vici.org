<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;

class RegisterPage extends PageRenderer
{
    private Session $session;
    private string $template = 'register.tpl';

    public function __construct(Session $session)
    {
        $this->session = $session;

        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);

        // Vooraf ingevulde waarden (placeholder, later via autosave/API)
        $this->assign('form_accountname_previous', '');
        $this->assign('form_realname_previous', '');
        $this->assign('form_email_previous', '');
        $this->assign('error_messages', []);
        $this->assign('is_verified_real_user', $this->session->isVerifiedRealUser());
    }
}
