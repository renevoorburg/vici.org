<?php

namespace Vici\Model\License;

use Vici\DB\DBConnector;
use PDO;
use Vici\Model\License\License;
use Vici\Model\License\LicenseCollection;

class LicenseRepository
{
    private DBConnector $db;

    public function __construct(DBConnector $db)
    {
        $this->db = $db;
    }

    public function findById(int $id): ?License
    {
        $stmt = $this->db->prepare("SELECT * FROM licenses WHERE license_id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->mapRowToLicense($row);
        }
        return null;
    }

    public function findAll(): LicenseCollection
    {
        $stmt = $this->db->query('SELECT * FROM licenses');
        $licenses = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $licenses[] = $this->mapRowToLicense($row);
        }
        return new LicenseCollection($licenses);
    }

    private function mapRowToLicense(array $row): License
    {
        $license = new License();
        $license->id = (int)$row['license_id'];
        $license->name = $row['license_short'];
        $license->shortName = $row['license_abbr'];
        $license->uri = $row['license_url'];
        $license->isUserSelectable = (bool)$row['license_uploadable'];
        return $license;
    }
}
