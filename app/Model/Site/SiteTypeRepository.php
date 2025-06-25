<?php

namespace Vici\Model\Site;

use Vici\DB\DBConnector;
use PDO;

class SiteTypeRepository
{
    /**
     * @var SiteType[] indexed by id
     */
    private array $siteTypes = [];

    public function __construct(DBConnector $db)
    {
        $stmt = $db->prepare("SELECT pkind_id, pkind_name, pkind_low, pkind_high, pkind_zindex, pkind_group, pkind_sort, pkind_line FROM pkinds ORDER BY pkind_group, pkind_sort");
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $siteType = new SiteType();
            $siteType->id = (int)$row['pkind_id'];
            $siteType->title = $row['pkind_name'];
            $siteType->shortDescription = null; // No equivalent in pkinds
            $siteType->longDescription = null; // No equivalent in pkinds
            $siteType->parentId = null; // No parent in pkinds
            $siteType->sortOrder = (int)$row['pkind_sort'];
            $this->siteTypes[$siteType->id] = $siteType;
        }
    }

    /**
     * @return SiteType[]
     */
    public function getAll(): array
    {
        return array_values($this->siteTypes);
    }

    public function getById(int $id): ?SiteType
    {
        return $this->siteTypes[$id] ?? null;
    }

    /**
     * @return int[]
     */
    public function getIds(): array
    {
        return array_keys($this->siteTypes);
    }

    public function getName(int $id): ?string
    {
        return $this->siteTypes[$id]->title ?? null;
    }

    public function getSmallZoom(int $id): ?int
    {
        // pkind_low
        return isset($this->siteTypes[$id]) ? (int)$this->siteTypes[$id]->smallZoom : null;
    }

    public function getBigZoom(int $id): ?int
    {
        // pkind_high
        return isset($this->siteTypes[$id]) ? (int)$this->siteTypes[$id]->bigZoom : null;
    }
}
