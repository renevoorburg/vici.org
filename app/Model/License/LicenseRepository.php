<?php

namespace Vici\Model\License;

use PDO;

class LicenseRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Vind een enkele licentie op basis van ID.
     * @param int $id
     * @return License|null
     */
    public function findById(int $id): ?License
    {
        $stmt = $this->pdo->prepare('SELECT * FROM licenses WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->mapRowToLicense($row);
        }
        return null;
    }

    /**
     * Vind alle licenties.
     * @return LicenseCollection
     */
    public function findAll(): LicenseCollection
    {
        $stmt = $this->pdo->query('SELECT * FROM licenses');
        $licenses = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $licenses[] = $this->mapRowToLicense($row);
        }
        return new LicenseCollection($licenses);
    }

    /**
     * Map een database-row naar een License object
     * @param array $row
     * @return License
     */
    private function mapRowToLicense(array $row): License
    {
        $license = new License();
        $license->id = (int)$row['id'];
        $license->name = $row['name'];
        $license->url = $row['url'];
        $license->text = $row['text'];
        // Voeg hier extra properties toe indien nodig
        return $license;
    }
}
