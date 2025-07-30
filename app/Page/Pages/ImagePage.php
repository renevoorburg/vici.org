<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;

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
        $imageRepo = new \Vici\Model\Site\Image\ImageRepository($db);
        $image = $imageRepo->findById($session->getRequestedItem());


        $this->assign('image', $image);

    }

}