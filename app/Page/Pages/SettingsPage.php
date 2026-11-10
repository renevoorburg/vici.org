<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;
use Vici\Model\User\UserRepository;
use Vici\Model\User\UserBio;
use Vici\Model\User\UserBioRepository;

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

            if (!$session->isCsrfTokenValid($_POST['csrf_token'] ?? null)) {
                $error = $session->translator->get('Invalid form token. Please reload the page and try again.');
            } elseif (mb_strlen(preg_replace('/\s+/', ' ', $realNameTrim)) < 4 || $realNameTrim !== trim($realNameTrim)) {
                $error = $session->translator->get("Display name must be at least 4 characters.");
            } else {
                $realName = preg_replace('/\s+/', ' ', $realNameTrim);
                $repo = new UserRepository($session->getDBConnector());
                $repo->updateRealName($user->getId(), $realName);
                $user->setRealName($realName);
                $session->setUser($user);

                $bio = new UserBio();
                $bio->bio = trim((string)($_POST['bio'] ?? ''));
                $labels = (array)($_POST['link_label'] ?? []);
                $urls = (array)($_POST['link_url'] ?? []);
                $bio->links = array_map(fn($i) => [
                    'label' => $labels[$i] ?? '',
                    'url' => $urls[$i] ?? '',
                ], range(0, 3));
                $bio->public = isset($_POST['bio_public']);
                $bio->contact = isset($_POST['bio_contact']);
                (new UserBioRepository($session->getDBConnector()))->save($user->getId(), $bio);

                $return = $_GET['return'] ?? '';
                header("Location: " . (preg_match('#^/[^/]#', $return) ? $return : '/'));
                exit;
            }
        }

        $bio = (new UserBioRepository($session->getDBConnector()))->get($user->getId());
        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);
        $this->assign('settings_realname', $user->getRealName());
        $this->assign('settings_bio', $bio->bio);
        $this->assign('settings_links', array_pad($bio->links, 4, ['label' => '', 'url' => '']));
        $this->assign('settings_bio_public', $bio->public);
        $this->assign('settings_profile_url', '/user/' . $user->getId());
        $this->assign('settings_bio_contact', $bio->contact);
        $this->assign('error_message', $error);
    }
}
