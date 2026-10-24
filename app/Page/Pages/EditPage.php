<?php

namespace Vici\Page\Pages;

use Vici\Page\PageRenderer;
use Vici\Session\Session;
use Vici\Model\Site\SiteRepository;
use Vici\Model\Site\SiteTypeRepository;
use Vici\Model\Site\Edit\SiteFormData;
use Vici\Model\Site\Image\ImageUploader;
use Vici\Model\Site\Line\KmlImporter;
use Vici\Model\License\LicenseRepository;
use Vici\Identifiers\ExternalIdentifiers;

class EditPage extends PageRenderer
{
    private Session $session;
    private string $template = 'edit.tpl';
    private array $errors = [];
    private ?string $message = null;

    public function __construct(Session $session, ?int $siteId = null)
    {
        $this->session = $session;
        parent::__construct($this->template, $session);
        $this->assignTranslatedTemplateVars($this->template);

        $db = $session->getDBConnector();
        $siteRepo = new SiteRepository($db);
        $site = $siteId !== null ? $siteRepo->findById($siteId) : null;

        if ($siteId !== null && $site === null) {
            http_response_code(404);
            $this->assign('edit_fatal', $this->session->translator->get('Site not found.'));
            return;
        }

        $tab = $session->getRequestedVariant();
        if (!in_array($tab, ['image', 'lines'], true)) {
            $tab = 'data';
        }
        if ($site === null) {
            $tab = 'data';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost($tab, $site, $siteRepo, $db);
            return;
        }

        if ($site === null && empty($_GET['kind'])) {
            $this->assignKindChooser($db);
            return;
        }

        $this->assignFormVars($site, $tab, $db);
    }

    private function handlePost(string $tab, $site, SiteRepository $siteRepo, $db): void
    {
        switch ($tab) {
            case 'image':
                $this->handleImagePost($site, $db);
                break;
            case 'lines':
                $this->handleLinesPost($site);
                break;
            default:
                $this->handleDataPost($site, $siteRepo);
                break;
        }
        $this->assignFormVars($site, $tab, $db);
    }

    private function handleDataPost($site, SiteRepository $siteRepo): void
    {
        $siteId = $site !== null ? $site->id : null;
        $data = SiteFormData::fromPost($_POST, $siteId);

        if ($data->hasErrors()) {
            $this->errors = $data->getErrors();
            return;
        }

        $userId = $this->session->getUser()->getId();
        $newId = $siteRepo->save($data, $userId, $this->session->getLanguage());

        header('Location: /vici/' . $newId);
        exit;
    }

    private function handleImagePost($site, $db): void
    {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = 'Please choose an image file to upload.';
            return;
        }

        $title = trim($_POST['frm_title'] ?? '');
        if (strlen($title) < 3) {
            $this->errors[] = 'The title is too short.';
        }

        $ownWork = ($_POST['frm_ownership'] ?? '1') === '1';
        if ($ownWork && !isset($_POST['frm_agree'])) {
            $this->errors[] = 'You need to agree with the license.';
        }
        if (!$ownWork && !isset($_POST['frm_licensed'])) {
            $this->errors[] = 'No license selected.';
        }

        $filename = $_FILES['file']['name'];
        if (preg_match('/\.(jpe?g|gif|png)$/i', $filename) === 0) {
            $this->errors[] = 'Unsupported file type.';
        }

        if (count($this->errors) > 0) {
            return;
        }

        $uploader = new ImageUploader();
        $ok = $uploader->store(
            $_FILES['file']['tmp_name'],
            $filename,
            $this->session->getUser()->getId(),
            $site->id,
            $title,
            trim($_POST['frm_text'] ?? ''),
            $this->session->getLanguage(),
            $ownWork,
            trim($_POST['frm_owner'] ?? ''),
            (int)($_POST['frm_license'] ?? ImageUploader::DEFAULT_LICENSE),
            trim($_POST['frm_attribute'] ?? ''),
            trim($_POST['frm_source'] ?? '')
        );

        if ($ok) {
            $siteRepo = new SiteRepository($this->session->getDBConnector());
            $siteRepo->touchEditStamp($site->id, $this->session->getUser()->getId());
            $this->message = 'Image uploaded successfully.';
        } else {
            $this->errors[] = $uploader->getErrorMessage();
        }
    }

    private function handleLinesPost($site): void
    {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = 'Please choose a KML file to upload.';
            return;
        }
        if (preg_match('/\.kml$/i', $_FILES['file']['name']) === 0) {
            $this->errors[] = 'Unsupported file type, please upload a KML file.';
            return;
        }

        $lineKind = (int)($_POST['frm_linekind'] ?? 5);
        if ($lineKind < 1 || $lineKind > 5) {
            $lineKind = 5;
        }

        $importer = new KmlImporter();
        $ok = $importer->replaceLinesForSite(
            $_FILES['file']['tmp_name'],
            $site->id,
            $this->session->getUser()->getId(),
            $lineKind,
            (int)($_POST['frm_license'] ?? ImageUploader::DEFAULT_LICENSE),
            trim($_POST['frm_owner'] ?? ''),
            trim($_POST['frm_attribute'] ?? '')
        );

        if ($ok) {
            $siteRepo = new SiteRepository($this->session->getDBConnector());
            $siteRepo->touchEditStamp($site->id, $this->session->getUser()->getId());
            $this->message = 'Lines were processed successfully. Any previous lines for this site were replaced.';
        } else {
            $this->errors[] = $importer->getErrorMessage();
        }
    }

    private function assignFormVars($site, string $tab, $db): void
    {
        $typeRepo = new SiteTypeRepository($db);
        $siteTypes = [];
        $typeTitles = [];
        foreach ($typeRepo->getAll() as $type) {
            $title = $this->session->translator->get($type->title);
            $siteTypes[] = ['id' => $type->id, 'title' => $title];
            $typeTitles[$type->id] = $title;
        }

        $licenseRepo = new LicenseRepository($db);
        $licenses = [];
        foreach ($licenseRepo->findAll() as $license) {
            if ($license->isUserSelectable) {
                $licenses[] = ['id' => $license->id, 'title' => $license->name];
            }
        }

        $isNew = $site === null;
        $lang = $this->session->getLanguage();
        $locale = (!$isNew) ? $site->locales->getByLanguage($lang) : null;

        $posted = $_SERVER['REQUEST_METHOD'] === 'POST';

        $extIds = [];
        if ($posted) {
            for ($i = 0; $i <= 10; $i++) {
                $extIds[] = $_POST['extid' . $i] ?? '';
            }
        } elseif (!$isNew) {
            foreach ($site->identifiers as $identifier) {
                if ($identifier !== null) {
                    $extIds[] = $identifier->uri;
                }
            }
        }
        while (count($extIds) < 11) {
            $extIds[] = '';
        }

        $this->assign('edit_tab', $tab);
        $this->assign('is_new', $isNew);
        $this->assign('pntid', $isNew ? '' : $site->id);
        $this->assign('site_name', !$isNew ? $site->defaultTitle : '');
        $this->assign('siteTypes', $siteTypes);
        $this->assign('licenses', $licenses);

        $this->assign('f_title', $posted ? ($_POST['kop_edit'] ?? '') : ($locale->title ?? (!$isNew ? $site->defaultTitle : '')));
        $this->assign('f_summary', $posted ? ($_POST['short_edit'] ?? '') : ($locale->summary ?? (!$isNew ? $site->defaultSummary : '')));
        $this->assign('f_annotation', $posted ? ($_POST['edit_full'] ?? '') : ($locale->description ?? ''));
        $this->assign('f_kind', $posted ? (int)($_POST['kindselect'] ?? 0) : (!$isNew ? $site->type->id : (int)($_GET['kind'] ?? 0)));
        $this->assign('f_coords', $posted ? ($_POST['coords_frm'] ?? '') : (!$isNew ? $site->representativeLocation->latitude . ', ' . $site->representativeLocation->longitude : ''));
        $this->assign('f_visible', $posted ? (int)($_POST['visibilityselect'] ?? 1) : (!$isNew ? (int)$site->isVisible : 1));
        $this->assign('f_accuracy', $posted ? ($_POST['accuracyselect'] ?? '') : (!$isNew ? $site->representativeLocation->qualifier : ''));
        $this->assign('accuracy_options', array_map(
            fn($i) => $this->session->translator->get("accuracy_$i"),
            range(0, 5)
        ));
        $this->assign('f_start', $posted ? ($_POST['start_yr'] ?? '') : (!$isNew ? ($site->period->startQualifier ?: $site->period->startYear) : ''));
        $this->assign('f_end', $posted ? ($_POST['end_yr'] ?? '') : (!$isNew ? $this->endYearDisplay($site) : ''));
        $this->assign('ext_ids', $extIds);

        $this->assign('form_errors', array_map(
            fn($e) => $this->session->translator->get($e),
            $this->errors
        ));
        $this->assign('form_message', $this->message !== null
            ? $this->session->translator->get($this->message) : null);
    }

    /**
     * Kind chooser for new sites: grouped cards with marker icon, example
     * image and explanation, linking to /add?kind=N. Mirrors the legacy
     * new.php selection page.
     */
    private function assignKindChooser($db): void
    {
        $typeRepo = new SiteTypeRepository($db);
        $typeTitles = [];
        foreach ($typeRepo->getAll() as $type) {
            $typeTitles[$type->id] = $this->session->translator->get($type->title);
        }

        $groups = [
            ['title' => $this->session->translator->get('Settlements'), 'items' => [
                [3, 'uploads/cut.jpg'], [9, 'uploads/vicus_micia_031.jpg'], [14, 'uploads/wijster.png'],
            ]],
            ['title' => $this->session->translator->get('Military buildings'), 'items' => [
                [4, 'uploads/portchester_castle_02.jpg'], [15, 'uploads/2012_20_0126.jpg'], [25, 'uploads/dsc_6439.jpg'],
            ]],
            ['title' => $this->session->translator->get('Infrastructural'), 'items' => [
                [1, 'uploads/Aqueduct_de_les_Ferreres.jpg'], [23, 'uploads/22671093.jpg'], [22, 'uploads/Alcantara_Bridge.jpg'],
            ]],
            ['title' => $this->session->translator->get('Roman buildings'), 'items' => [
                [13, 'uploads/9290725317_90c5c45a56_h.jpg'], [7, 'uploads/Herberg_Nijmegen.jpg'],
                [12, 'uploads/Merida_Roman_Theatre.jpg'], [2, 'uploads/baths_of_diocletian_antmoose1.jpg'],
                [11, 'uploads/Baalbek_Temple_of_Bacchus.jpg'], [6, 'uploads/2011_16_0008.jpg'],
                [5, 'uploads/img_2163_fotor.jpg'], [21, 'uploads/Trier_Porta-Negra.jpg'],
            ]],
            ['title' => $this->session->translator->get('Smaller objects'), 'items' => [
                [16, 'uploads/aphrodisias_sebasteion02.jpg'], [20, 'uploads/roemischer_meilenstein_juelich_2009.jpg'],
                [10, 'uploads/de_meern1_2.png'], [17, 'uploads/40d_4215.jpg'], [18, 'uploads/dsc_6634.jpg'],
            ]],
            ['title' => $this->session->translator->get('Current day objects or locations'), 'items' => [
                [24, 'uploads/cesar_sa_mort.png'], [8, 'uploads/museum_het_valkhof_nijmegen_netherlands_9566990543_.jpg'],
                [19, 'uploads/Nieuw_Wulven2.jpg'],
            ]],
        ];

        $chooserGroups = [];
        foreach ($groups as $group) {
            $items = [];
            foreach ($group['items'] as [$kindId, $image]) {
                $items[] = [
                    'id' => $kindId,
                    'title' => $typeTitles[$kindId] ?? '',
                    'image' => $image,
                    'desc' => $this->session->translator->get('markerdef.' . $kindId),
                ];
            }
            $chooserGroups[] = ['title' => $group['title'], 'items' => $items];
        }

        $this->assign('show_kind_chooser', true);
        $this->assign('kind_groups', $chooserGroups);
        $this->assign('is_new', true);
        $this->assign('edit_tab', 'data');
        $this->assign('form_errors', []);
        $this->assign('form_message', null);
    }

    private function endYearDisplay($site): string
    {
        $endYear = $site->period->endYear;
        if ($endYear == 9999) {
            return 'open';
        }
        if ($endYear == 0) {
            return 'unknown';
        }
        return $site->period->endQualifier ?: (string)$endYear;
    }
}
