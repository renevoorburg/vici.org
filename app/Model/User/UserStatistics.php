<?php

namespace Vici\Model\User;

use Vici\DB\DBConnector;
use PDO;

class UserStatistics
{
    private DBConnector $db;

    public function __construct(DBConnector $db)
    {
        $this->db = $db;
    }

    /**
     * Contribution counts and ranking per data type for a user.
     * Each entry: ['count' => int, 'rank' => int|null, 'contributors' => int]
     * rank is 1-based among contributors; null when the user has no contributions.
     */
    public function forUser(int $userId): array
    {
        return [
            'sites_added'  => $this->countWithRank('pmetadata', 'pmeta_creator', $userId),
            'sites_edited' => $this->countWithRank('pmetadata', 'pmeta_editor', $userId),
            'images'       => $this->countWithRank('img_data', 'imgd_uploader', $userId),
            'lines'        => $this->countWithRank('plines', 'line_uploader', $userId),
            'texts'        => $this->countWithRank('ptexts', 'ptxt_editor', $userId),
        ];
    }

    private function countWithRank(string $table, string $column, int $userId): array
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `$table` WHERE `$column` = :uid");
        $stmt->execute(['uid' => $userId]);
        $count = (int)$stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COUNT(DISTINCT `$column`) FROM `$table` WHERE `$column` IS NOT NULL");
        $stmt->execute();
        $contributors = (int)$stmt->fetchColumn();

        $rank = null;
        if ($count > 0) {
            $stmt = $this->db->prepare("
                SELECT 1 + COUNT(*) FROM (
                    SELECT `$column` FROM `$table`
                    WHERE `$column` IS NOT NULL
                    GROUP BY `$column`
                    HAVING COUNT(*) > :cnt
                ) t
            ");
            $stmt->execute(['cnt' => $count]);
            $rank = (int)$stmt->fetchColumn();
        }

        return ['count' => $count, 'rank' => $rank, 'contributors' => $contributors];
    }
}
