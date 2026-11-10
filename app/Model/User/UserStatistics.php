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

    /**
     * Contributions per calendar day over the last 371 days.
     * Returns ['YYYY-MM-DD' => int]. Note: only the most recent edit per
     * record counts — there is no edit history in the schema.
     */
    public function dailyContributions(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT d, SUM(n) AS c FROM (
                SELECT DATE(pmeta_create_date) AS d, COUNT(*) AS n
                FROM pmetadata WHERE pmeta_creator = :uid1 GROUP BY d
                UNION ALL
                SELECT DATE(pmeta_edit_date) AS d, COUNT(*) AS n
                FROM pmetadata WHERE pmeta_editor = :uid2
                    AND DATE(pmeta_edit_date) <> DATE(pmeta_create_date) GROUP BY d
                UNION ALL
                SELECT DATE(imgd_date) AS d, COUNT(*) AS n
                FROM img_data WHERE imgd_uploader = :uid3 GROUP BY d
                UNION ALL
                SELECT DATE(line_date) AS d, COUNT(*) AS n
                FROM plines WHERE line_uploader = :uid4 GROUP BY d
                UNION ALL
                SELECT DATE(ptxt_edit_date) AS d, COUNT(*) AS n
                FROM ptexts WHERE ptxt_editor = :uid5 GROUP BY d
            ) x
            WHERE d >= DATE_SUB(CURDATE(), INTERVAL 371 DAY)
            GROUP BY d
        ");
        $stmt->execute(['uid1' => $userId, 'uid2' => $userId, 'uid3' => $userId, 'uid4' => $userId, 'uid5' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    private const ALLOWED_COLUMNS = [
        'pmetadata' => ['pmeta_creator', 'pmeta_editor'],
        'img_data'  => ['imgd_uploader'],
        'plines'    => ['line_uploader'],
        'ptexts'    => ['ptxt_editor'],
    ];

    private function countWithRank(string $table, string $column, int $userId): array
    {
        if (!in_array($column, self::ALLOWED_COLUMNS[$table] ?? [], true)) {
            throw new \InvalidArgumentException("Unsupported statistics column $table.$column");
        }
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
