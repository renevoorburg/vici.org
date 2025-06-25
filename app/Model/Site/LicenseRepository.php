<?php

namespace Vici\Model\Site;

use Vici\DB\DBConnector;
use PDO;

class LicenseRepository
{
    private DBConnector $db;

    public function __construct(DBConnector $db)
    {
        $this->db = $db;
    }

    /**
     * Haal een specifieke licentie op basis van id
     */
    public function getById(int $id): ?License
    {
        $stmt = $this->db->prepare("SELECT * FROM licenses WHERE license_id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return $this->rowToLicense($row);
    }

    /**
     * Haal een licentie op basis van de abbreviation (license_abbr)
     */
    public function getByAbbreviation(string $abbreviation): ?License
    {
        $stmt = $this->db->prepare("SELECT * FROM licenses WHERE license_abbr = :abbr");
        $stmt->execute(['abbr' => $abbreviation]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        return $this->rowToLicense($row);
    }

    /**
     * Haal alle licenties op, eventueel alleen de userselectable
     */
    public function getAll(bool $onlyUserSelectable = false): array
    {
        $sql = "SELECT * FROM licenses";
        if ($onlyUserSelectable) {
            $sql .= " WHERE license_uploadable = 1";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $this->rowToLicense($row);
        }
        return $result;
    }

    private function rowToLicense(array $row): License
    {
        $license = new License();
        $license->id = (int)$row['license_id'];
        $license->title = $row['license_short'];
        $license->abbreviation = $row['license_abbr'] ?? null;
        $license->url = $row['license_url'] ?? null;
        $license->isUserSelectable = isset($row['license_uploadable']) ? (bool)$row['license_uploadable'] : null;
        return $license;
    }
}
