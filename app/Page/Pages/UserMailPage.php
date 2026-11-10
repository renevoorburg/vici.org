<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;
use Vici\Model\User\UserRepository;
use Vici\Model\User\UserBioRepository;
use Vici\Mail\Mailer;

class UserMailPage extends PageRenderer
{
    private string $template = 'usermail.tpl';

    public function __construct(Session $session)
    {
        $db = $session->getDBConnector();
        $sender = $session->getUser();

        $userRepo = new UserRepository($db);
        $recipient = $userRepo->findById($session->getRequestedItem());
        $bio = $recipient ? (new UserBioRepository($db))->get($recipient->getId()) : null;

        $allowed = $recipient !== null && !$recipient->isDeleted()
            && $bio->contact && $recipient->getId() !== $sender->getId();
        if (!$allowed) {
            header('Location: /user/' . $session->getRequestedItem());
            exit;
        }

        $error = '';
        $sent = false;
        $formMessage = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formMessage = trim((string)($_POST['message'] ?? ''));
            if (mb_strlen($formMessage) < 10) {
                $error = $session->translator->get('Please enter a message of at least 10 characters.');
            } else {
                $body = sprintf(
                        $session->translator->get("%s sent you a message via your Vici.org profile (%s):\n\n"),
                        $sender->getRealName(),
                        $session->getViciBase() . '/user/' . $sender->getId()
                    ) . mb_substr($formMessage, 0, 4000) . "\n";
                $subject = sprintf(
                    $session->translator->get('Vici.org: message from %s'),
                    $sender->getRealName()
                );
                $sent = (new Mailer($session))->send(
                    $recipient->getEmail(), $subject, $body, $sender->getEmail()
                );
                if (!$sent) {
                    $error = $session->translator->get('The message could not be sent.');
                }
            }
        }

        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);
        $this->assign('recipient_id', $recipient->getId());
        $this->assign('recipient_name', $recipient->getRealName());
        $this->assign('form_message', $formMessage);
        $this->assign('error_message', $error);
        $this->assign('sent', $sent);
        $this->assign('title', $session->translator->get('Mail user'));
    }
}
