<?php

namespace Vici\Model\User;

use PDO;
use Vici\DB\DBConnector;

class UserBioRepository
{
    private const MAX_LINKS = 4;

    private DBConnector $db;

    public function __construct(DBConnector $db)
    {
        $this->db = $db;
    }

    public function get(int $userId): UserBio
    {
        $stmt = $this->db->prepare("SELECT * FROM usrbio WHERE ubio_user = :uid LIMIT 1");
        $stmt->execute(['uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $bio = new UserBio();
        if (!$row) {
            return $bio;
        }
        $bio->bio = (string)($row['ubio_bio'] ?? '');
        $links = json_decode((string)($row['ubio_links'] ?? ''), true);
        $bio->links = is_array($links) ? $links : [];
        $bio->public = (bool)$row['ubio_public'];
        $bio->contact = (bool)$row['ubio_contact'];
        return $bio;
    }

    public function save(int $userId, UserBio $bio): void
    {
        $links = array_slice(array_values(array_filter(array_map(fn($l) => [
            'label' => mb_substr(trim((string)($l['label'] ?? '')), 0, 40),
            'url' => mb_substr(trim((string)($l['url'] ?? '')), 0, 255),
        ], $bio->links), fn($l) => $l['label'] !== '' && $l['url'] !== '')), 0, self::MAX_LINKS);

        $this->db->prepare(
            "INSERT INTO usrbio (ubio_user, ubio_bio, ubio_links, ubio_public, ubio_contact)
             VALUES (:uid, :bio, :links, :public, :contact)
             ON DUPLICATE KEY UPDATE ubio_bio = VALUES(ubio_bio), ubio_links = VALUES(ubio_links),
                ubio_public = VALUES(ubio_public), ubio_contact = VALUES(ubio_contact)"
        )->execute([
            'uid' => $userId,
            'bio' => mb_substr($bio->bio, 0, 4000),
            'links' => json_encode($links),
            'public' => $bio->public ? 1 : 0,
            'contact' => $bio->contact ? 1 : 0,
        ]);
    }
}
