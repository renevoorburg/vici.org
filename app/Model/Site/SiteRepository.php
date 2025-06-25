<?php

namespace Vici\Model\Site;

use Vici\DB\DBConnector;
use PDO;

class SiteRepository
{
    private DBConnector $db;

    public function __construct(DBConnector $db)
    {
        $this->db = $db;
    }

    /**
     * Haal een Site op met alle locales en type (nog zonder images/lines)
     */
    public function getById(int $id): ?Site
    {
        // Haal basisdata uit points
        $stmt = $this->db->prepare("SELECT * FROM points WHERE pnt_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        // Haal type op
        $typeRepo = new SiteTypeRepository($this->db);
        $type = $typeRepo->getById((int)$row['pnt_kind']);


        // Haal alle SiteLocales op via SiteLocaleRepository (associatieve array per taal)
        $localeRepo = new SiteLocaleRepository($this->db);
        $locales = $localeRepo->getBySiteId($id);

        // Maak Site object
        $site = new Site();
        $site->id = (int)$row['pnt_id'];
        $site->type = $type;
        $site->locales = $locales; // ['nl' => SiteLocale, 'en' => SiteLocale, ...]
        $site->images = []; // images later
        return $site;
    }


}
