<?php

namespace Vici\Model\Site\Identifiers;

use Vici\DB\DBConnector;
use PDO;
use Vici\Model\Site\Identifiers\Identifier;
use Vici\Model\Site\Identifiers\IdentifierCollection;

use Vici\Identifiers\Normalizers\Livius;
use Vici\Identifiers\ExternalIdentifiers;

class IdentifierRepository
{
    private DBConnector $db;

    public function __construct(DBConnector $db)
    {
        $this->db = $db;
    }

    public function findBySite(int $id): ?IdentifierCollection
    {
        $stmt = $this->db->prepare("
            SELECT pmeta_extids, pmeta_pleiades, pmeta_livius, pmeta_romaq, pmeta_dare
            FROM pmetadata
            WHERE pmeta_pnt_id = :site_id
        ");
        $stmt->execute(['site_id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $identifiers = $row ? $this->mapRowToIdentifiers($row) : [];
        return new IdentifierCollection($identifiers);
    }

    private function mapRowToIdentifiers(array $row): array
    {
        $identifiers = [];
        if ($row['pmeta_pleiades']) {
            $identifier = new Identifier();
            $identifier->prefix = 'pleiades';
            $identifier->namespace = 'https://pleiades.stoa.org/places/';
            $identifier->uri = $identifier->namespace . $row['pmeta_pleiades'];
            $identifiers[] = $identifier;
        }

        if ($row['pmeta_livius']) {
            $normalizer = new Livius();
            $identifier = new Identifier();
            $identifier->prefix = 'livius';
            $identifier->namespace = 'https://livius.org/articles/';
            $identifier->uri = $normalizer->idToUrl($row['pmeta_livius']);
            $identifiers[] = $identifier;
        }

        if ($row['pmeta_romaq']) {
            $identifier = new Identifier();
            $identifier->prefix = 'romaq';
            $identifier->namespace = 'https://www.romaq.org/the-project/aqueducts/';
            $identifier->uri = $identifier->namespace . $row['pmeta_romaq'];
            $identifiers[] = $identifier;
        }

        if ($row['pmeta_dare']) {
            $identifier = new Identifier();
            $identifier->prefix = 'dare';
            $identifier->namespace = 'http://imperium.ahlfeldt.se/places/';
            $identifier->uri = $identifier->namespace . $row['pmeta_dare'];
            $identifiers[] = $identifier;
        }

        if ($row['pmeta_extids']) {
            $other_ids = ExternalIdentifiers::withDbParams($row['pmeta_extids']);
            $urls = $other_ids->getAllUrlsArray();
            foreach ($urls as $url) {
                $identifier = new Identifier();
                $identifier->prefix = 'http';
                $identifier->namespace = $url;
                $identifier->uri = $url;
                $identifiers[] = $identifier;
            }
          
        }

        return $identifiers;
    }

}