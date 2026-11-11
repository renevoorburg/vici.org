<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;
use Vici\Model\User\User;
use Vici\Model\Site\Image\ImageRepository;

class ImagePage extends PageRenderer
{

    private Session $session;
    private string $template = 'image.tpl';

    public function __construct(Session $session)
    {
        $this->session = $session;
        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);

        $db = $session->getDBConnector();
        $imageRepo = new ImageRepository($db);
        $image = $imageRepo->findById($session->getRequestedItem());

        $user = $session->hasUser() ? $session->getUser() : null;
        $isAdmin = $user !== null && $user->isAdmin();

        // hidden images: admins may still view (and unhide) them
        if ($image === null && $isAdmin) {
            $image = $imageRepo->findById($session->getRequestedItem(), false);
        }

        $isUploader = $user !== null && $image !== null && $image->uploader !== null
            && $image->uploader->getId() === $user->getId();
        $canEdit = $isAdmin || $isUploader
            || ($user !== null && $user->getLevel() >= User::LEVEL_CONFIRMED);

        if ($isAdmin && $image !== null && $_SERVER['REQUEST_METHOD'] === 'POST'
            && isset($_POST['toggle_hidden'])
            && $session->isCsrfTokenValid($_POST['csrf_token'] ?? null)) {
            $imageRepo->setHidden($image->id, $image->isPublished);
            header('Location: /image/' . $image->id);
            exit;
        }

        if ($image === null) {
            http_response_code(404);
            $this->setTemplate('notfound.tpl');
            $this->assign('title', $this->session->translator->get('Not found.'));
            $this->assign('notfound_message', $this->session->translator->get('Image not found.'));
            return;
        }

        $this->assign('image', $image);
        $this->assign('can_edit', $canEdit);
        $this->assign('is_admin', $isAdmin);

    }

}
