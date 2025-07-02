<?php

namespace Vici\Model\Site\Image;

use PDO;
use Vici\Model\Site\Image\Image;
use Vici\Model\Site\Image\ImageCollection;

class ImageRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Vind een enkele afbeelding op basis van ID.
     * @param int $id
     * @return Image|null
     */
    public function findById(int $id): ?Image
    {
        $stmt = $this->pdo->prepare('SELECT * FROM images WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->mapRowToImage($row);
        }
        return null;
    }

    /**
     * Vind alle afbeeldingen bij een bepaalde site.
     * @param int $siteId
     * @return ImageCollection
     */
    public function findBySite(int $siteId): ImageCollection
    {
        $stmt = $this->pdo->prepare('SELECT * FROM images WHERE site_id = :site_id');
        $stmt->execute(['site_id' => $siteId]);
        $images = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $images[] = $this->mapRowToImage($row);
        }
        return new ImageCollection($images);
    }

    /**
     * Map een database-row naar een Image object
     * @param array $row
     * @return Image
     */
    private function mapRowToImage(array $row): Image
    {
        $image = new Image();
        $image->id = (int)$row['id'];
        $image->title = $row['title'];
        $image->description = $row['description'];
        $image->language = $row['language'];
        $image->filepath = $row['filepath'];
        $image->isPublished = (bool)$row['is_published'];
        // $image->uploader = ... // moet apart worden opgehaald
        $image->isOwnWork = (bool)$row['is_own_work'];
        $image->source = $row['source'];
        $image->creator = $row['creator'];
        // $image->license = ... // moet apart worden opgehaald of geconstrueerd
        $image->dateAdded = $row['date_added'];
        $image->width = (int)$row['width'];
        $image->height = (int)$row['height'];
        $image->md5sum = $row['md5sum'];
        return $image;
    }
}
