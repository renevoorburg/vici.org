<?php

namespace Vici\Model\Site\Identifier;

use Vici\DB\DBConnector;
use PDO;
use Vici\Model\Site\Identifier\Identifier;
use Vici\Model\Site\Identifier\IdentifierCollection;

use Vici\Identifiers\NormalizersIndex;
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
            $normalizer = NormalizersIndex::getIndexedNormalizer('pleiades');
            $identifier = new Identifier();
            $identifier->prefix = $normalizer->getPrefix();
            $identifier->namespace = $normalizer->getNamespace();
            $identifier->uri = $identifier->namespace . $row['pmeta_pleiades'];
            $identifiers[] = $identifier;
        }

        if ($row['pmeta_livius']) {
            $normalizer = NormalizersIndex::getIndexedNormalizer('livius');
            $identifier = new Identifier();
            $identifier->prefix = $normalizer->getPrefix();
            $identifier->namespace = $normalizer->getNamespace();
            $identifier->uri = $normalizer->idToUrl($row['pmeta_livius']);
            $identifiers[] = $identifier;
        }

        if ($row['pmeta_romaq']) {
            $normalizer = NormalizersIndex::getIndexedNormalizer('romaq');
            $identifier = new Identifier();
            $identifier->prefix = $normalizer->getPrefix();
            $identifier->namespace = $normalizer->getNamespace();
            $identifier->uri = $identifier->namespace . $row['pmeta_romaq'];
            $identifiers[] = $identifier;
        }

        if ($row['pmeta_dare']) {
            $normalizer = NormalizersIndex::getIndexedNormalizer('dare');
            $identifier = new Identifier();
            $identifier->prefix = $normalizer->getPrefix();
            $identifier->namespace = $normalizer->getNamespace();
            $identifier->uri = $identifier->namespace . $row['pmeta_dare'];
            $identifiers[] = $identifier;
        }

        if ($row['pmeta_extids']) {
            $other_ids = ExternalIdentifiers::withDbParams($row['pmeta_extids']);
            $urls = $other_ids->getAllUrlsArray();
            foreach ($urls as $url) {
                foreach (NormalizersIndex::getNormalizerKeys() as $key) {
                    $normalizer = NormalizersIndex::getIndexedNormalizer($key);
                    if ($normalizer->isValidURL($url)) {
                        $identifier = new Identifier();
                        $identifier->prefix = $normalizer->getPrefix();
                        $identifier->namespace = $normalizer->getNamespace();
                        $identifier->uri = $url;
                        $identifiers[] = $identifier;
                        break;
                    }
                }
            }
        }

        return $identifiers;
    }

}