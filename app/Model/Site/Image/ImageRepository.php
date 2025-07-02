<?php

namespace Vici\Model\Site\Image;

use Vici\DB\DBConnector;
use PDO;
use Vici\Model\Site\Image\Image;
use Vici\Model\Site\Image\ImageCollection;

class ImageRepository
{
    private DBConnector $db;

    public function __construct(DBConnector $db)
    {
        $this->db = $db;
    }

    public function findById(int $id): ?Image
    {
        $stmt = $this->db->prepare("
            SELECT * 
            FROM images i
            LEFT JOIN img_data d ON i.img_id = d.imgd_imgid
            WHERE i.img_id = :id LIMIT 1
        ");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return $this->mapRowToImage($row);
        }
        return null;
    }

    public function findBySite(int $siteId): ImageCollection
    {
        $stmt = $this->db->prepare("SELECT * FROM images WHERE site_id = :site_id");
        $stmt->execute(['site_id' => $siteId]);
        $images = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $images[] = $this->mapRowToImage($row);
        }
        return new ImageCollection($images);
    }

    private function mapRowToImage(array $row): Image
    {
        $image = new Image();
        $image->id = (int)$row['img_id'];
        $image->filepath = $row['img_path'];
        $image->isPublished = !(bool)$row['img_hide'];

        $image->isOwnWork = (bool)$row['imgd_ownwork'];
        $image->source = $row['imgd_source'];
        $image->creator = $row['imgd_creator'];

        $image->title = $row['imgd_title'];
        $image->description = $row['imgd_description'];
        $image->language = $row['imgd_lang'];
        $image->md5sum = $row['imgd_md5sum'];
        $image->dateAdded = $row['imgd_date'];
        $image->width = (int)$row['imgd_width'];
        $image->height = (int)$row['imgd_height'];

        $image->data = $row['imgd_data'];
        // $image->license = ... // moet apart worden opgehaald of geconstrueerd
        // $image->uploader = ... // moet apart worden opgehaald
        return $image;
    }
}
