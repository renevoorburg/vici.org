<?php

namespace Vici\Model\Site\Image;

use Vici\DB\DBConnector;
use Vici\Storage\ImageStorage;
use Vici\Storage\LocalImageStorage;
use Vici\Storage\S3ImageStorage;

class ImageUploader
{
    public const UPLOAD_DIR = 'uploads';
    public const DEFAULT_LICENSE = 4; // CC BY-SA 3.0

    private string $errorMessage = '';

    private function storage(): ImageStorage
    {
        if (!empty($_ENV['S3_BUCKET'])) {
            return new S3ImageStorage();
        }
        return new LocalImageStorage();
    }

    private function slug(string $string): string
    {
        $unaccented = html_entity_decode(
            preg_replace(
                '~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i',
                '$1',
                htmlentities($string, ENT_QUOTES, 'UTF-8')
            ),
            ENT_QUOTES,
            'UTF-8'
        );
        return strtolower(trim(preg_replace('~[^0-9a-z.]+~i', '_', $unaccented), '_'));
    }

    private function gpsCoordinate(array $coordinate, string $ref): float
    {
        $value = 0;
        foreach (array_slice($coordinate, 0, 3) as $i => $part) {
            [$num, $dec] = explode('/', $part) + [1 => 1];
            $value += ($num / $dec) / pow(60, $i);
        }
        return ($ref === 'S' || $ref === 'W') ? -$value : $value;
    }

    /**
     * Store an uploaded image, link it to a site and move the file into the uploads dir.
     *
     * @return bool true on success, false on error (see getErrorMessage())
     */
    public function store(string $tmpPath, string $originalName, int $userId, int $siteId, string $title, string $description, string $lang, bool $ownWork, ?string $owner = null, ?int $license = null, ?string $attribution = null, ?string $source = null): bool
    {
        $filename = $this->slug($originalName);
        $md5sum = md5_file($tmpPath);

        $db = new DBConnector();

        $stmt = $db->prepare("SELECT imgd_imgid FROM img_data WHERE imgd_md5sum = :md5 AND imgd_uploader = :uid");
        $stmt->execute(['md5' => $md5sum, 'uid' => $userId]);
        if ($stmt->fetch()) {
            $this->errorMessage = 'Duplicate image, already uploaded using this account.';
            return false;
        }

        $storage = $this->storage();
        if ($storage->exists($filename)) {
            $filename = $md5sum . '_' . $filename;
        }

        $size = getimagesize($tmpPath);
        if ($size === false) {
            $this->errorMessage = 'Error: not a valid image file.';
            return false;
        }
        $width = $size[0];
        $height = $size[1];

        $exifText = '';
        $lat = null;
        $lng = null;
        $exif = @exif_read_data($tmpPath, 0, true);
        if ($exif !== false) {
            // flip width/height when the image is stored rotated
            if (isset($exif['IFD0']['Orientation']) && in_array((int)$exif['IFD0']['Orientation'], [5, 6, 7, 8], true)) {
                $width = $size[1];
                $height = $size[0];
            }
            if (!empty($exif['IFD0']['Make'])) {
                $exifText .= 'Camera: ' . $exif['IFD0']['Make'];
                if (!empty($exif['IFD0']['Model'])) {
                    $exifText .= ', ' . $exif['IFD0']['Model'];
                }
                $exifText .= '<br/>';
            }
            if (!empty($exif['COMPUTED']['ApertureFNumber'])) {
                $exifText .= 'Aperture: ' . $exif['COMPUTED']['ApertureFNumber'] . '<br/>';
            }
            if (isset($exif['GPS']['GPSLatitudeRef'], $exif['GPS']['GPSLatitude'], $exif['GPS']['GPSLongitudeRef'], $exif['GPS']['GPSLongitude'])) {
                $lat = $this->gpsCoordinate($exif['GPS']['GPSLatitude'], $exif['GPS']['GPSLatitudeRef']);
                $lng = $this->gpsCoordinate($exif['GPS']['GPSLongitude'], $exif['GPS']['GPSLongitudeRef']);
                $exifText .= 'Location: ' . $lat . ', ' . $lng . '<br/>';
            }
        }

        $license = $ownWork ? self::DEFAULT_LICENSE : (int)$license;

        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO images (img_path, img_hide) VALUES (:path, 0)");
            $stmt->execute(['path' => '/' . self::UPLOAD_DIR . '/' . $filename]);
            $imageId = (int)$db->lastInsertId();

            $stmt = $db->prepare("
                INSERT INTO img_data
                    (imgd_imgid, imgd_uploader, imgd_ownwork, imgd_license, imgd_source, imgd_creator, imgd_attribution,
                     imgd_width, imgd_height, imgd_lat, imgd_lng, imgd_title, imgd_description, imgd_metadata, imgd_lang, imgd_md5sum)
                VALUES
                    (:id, :uploader, :ownwork, :license, :source, :creator, :attribution,
                     :width, :height, :lat, :lng, :title, :description, :exif, :lang, :md5)
            ");
            $stmt->execute([
                'id' => $imageId,
                'uploader' => $userId,
                'ownwork' => (int)$ownWork,
                'license' => $license,
                'source' => $source,
                'creator' => $ownWork ? null : $owner,
                'attribution' => $attribution,
                'width' => $width,
                'height' => $height,
                'lat' => $lat,
                'lng' => $lng,
                'title' => $title,
                'description' => $description,
                'exif' => $exifText !== '' ? $exifText : null,
                'lang' => $lang,
                'md5' => $md5sum,
            ]);

            $stmt = $db->prepare("SELECT pil_img FROM pnt_img_lnk WHERE pil_dflt = 1 AND pil_pnt = :site_id");
            $stmt->execute(['site_id' => $siteId]);
            $isDefault = $stmt->fetch() ? 0 : 1;

            $stmt = $db->prepare("INSERT INTO pnt_img_lnk (pil_pnt, pil_img, pil_dflt) VALUES (:site_id, :image_id, :dflt)");
            $stmt->execute(['site_id' => $siteId, 'image_id' => $imageId, 'dflt' => $isDefault]);

            // store the object before committing, so a storage failure
            // does not leave DB rows pointing at a missing file
            if (!$storage->put($filename, $tmpPath)) {
                $db->rollBack();
                $this->errorMessage = 'Error: could not store image file.';
                return false;
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->errorMessage = 'Error: could not save image data.';
            return false;
        }
        return true;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}
