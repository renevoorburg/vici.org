<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;
use Vici\Model\User\User;
use Vici\Model\License\LicenseRepository;
use Vici\Model\Site\Image\ImageRepository;

class ImageEditPage extends PageRenderer
{

    private Session $session;
    private string $template = 'imageedit.tpl';

    public function __construct(Session $session)
    {
        $this->session = $session;
        parent::__construct($this->template, $session);

        $db = $session->getDBConnector();
        $imageRepo = new ImageRepository($db);
        $image = $imageRepo->findById($session->getRequestedItem());

        $user = $session->hasUser() ? $session->getUser() : null;
        $isAdmin = $user !== null && $user->isAdmin();

        // hidden images: admins may still reach (and unhide) them
        if ($image === null && $isAdmin) {
            $image = $imageRepo->findById($session->getRequestedItem(), false);
        }

        if ($image === null) {
            header('Location: /');
            exit;
        }

        $isUploader = $user !== null && $image->uploader !== null
            && $image->uploader->getId() === $user->getId();
        // uploader and admin may change everything; confirmed users may
        // change title and description only
        $canEditAll = $isAdmin || $isUploader;
        $canEdit = $canEditAll || ($user !== null && $user->getLevel() >= User::LEVEL_CONFIRMED);

        if (!$canEdit) {
            header('Location: /image/' . $image->id);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && $session->isCsrfTokenValid($_POST['csrf_token'] ?? null)) {
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            if (mb_strlen($title) < 3) {
                // keep the posted values so the user does not lose them
                $image->title = $title;
                $image->description = $description;
                $this->assign('form_error',
                    $this->session->translator->get('Title should be at least 3 characters.'));
            } else {
                if ($canEditAll) {
                    $image->title = $title;
                    $image->description = $description;
                    $image->license = (new LicenseRepository($db))->findById((int)($_POST['license'] ?? 0));
                    $imageRepo->updateMetadata($image);
                } else {
                    $imageRepo->updateText($image->id, $title, $description);
                }
                header('Location: /image/' . $image->id);
                exit;
            }
        }

        $this->assignTranslatedTemplateVars($this->template);
        $this->assign('image', $image);
        $this->assign('can_edit_all', $canEditAll);
        if ($canEditAll) {
            $this->assign('licenses', (new LicenseRepository($db))->findAll());
        }

    }

}
