<?php

namespace Vici\Model\Site\Line;

use Vici\DB\DBConnector;
use Vici\Geo\Shape;
use Vici\Geo\ShapePoint;
use Vici\Geo\ShapeReducer;
use geoPHP;

class KmlImporter
{
    private const MAX_KML_SIZE = 67108864; // 64 MB, after decompression

    private string $errorMessage = '';

    /**
     * Replace all lines of a site with the LineStrings found in a KML file.
     * Existing lines are hidden (line_hide=1), new lines are stored with
     * simplified geometry per zoom band in pline_data.
     *
     * @return bool true on success, false on error (see getErrorMessage())
     */
    public function replaceLinesForSite(string $filePath, int $siteId, int $userId, int $lineKind, int $license, string $owner, string $attribution): bool
    {
        $lines = $this->reduceFile($filePath);
        if ($lines === null) {
            return false;
        }

        $db = new DBConnector();
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("UPDATE plines SET line_hide=1 WHERE line_pnt_id=:site_id");
            $stmt->execute(['site_id' => $siteId]);

            $insertLine = $db->prepare("
                INSERT INTO plines (line_pnt_id, line_kind, line_hide, line_sw_lat, line_sw_lng, line_ne_lat, line_ne_lng, line_note, line_uploader, line_license, line_owner, line_attribution)
                VALUES (:site_id, :kind, 0, :sw_lat, :sw_lng, :ne_lat, :ne_lng, '', :uploader, :license, :owner, :attribution)
            ");
            $insertData = $db->prepare("
                INSERT INTO pline_data (pldata_pline_id, pldata_fromzoom, pldata_tozoom, pldata_points)
                VALUES (:line_id, :fromzoom, :tozoom, :points)
            ");

            foreach ($lines as $line) {
                $insertLine->execute([
                    'site_id' => $siteId,
                    'kind' => $lineKind,
                    'sw_lat' => $line['sw_lat'],
                    'sw_lng' => $line['sw_lng'],
                    'ne_lat' => $line['ne_lat'],
                    'ne_lng' => $line['ne_lng'],
                    'uploader' => $userId,
                    'license' => $license,
                    'owner' => $owner,
                    'attribution' => $attribution,
                ]);
                $lineId = (int)$db->lastInsertId();

                foreach ($line['linedata'] as $data) {
                    $insertData->execute([
                        'line_id' => $lineId,
                        'fromzoom' => $data['fromZoom'],
                        'tozoom' => $data['toZoom'],
                        'points' => $data['data'],
                    ]);
                }
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->errorMessage = 'Error: could not write lines to database.';
            return false;
        }
        return true;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    /**
     * Parse a KML file and return per LineString a bounding box plus a
     * set of simplifications for the zoom bands used by the tile service.
     */
    private function reduceFile(string $path): ?array
    {
        $file = $this->readKmlContent($path);
        if ($file === null) {
            return null;
        }

        try {
            $geom = geoPHP::load($file, 'kml');
        } catch (\Throwable $e) {
            $this->errorMessage = 'Error: could not parse KML file.';
            return null;
        }

        $result = [];
        if ($geom->geometryType() === 'LineString') {
            $result[] = $this->reduceGeometry($geom);
        } else {
            for ($i = 1; $i <= $geom->numGeometries(); $i++) {
                $curGeom = $geom->geometryN($i);
                if ($curGeom->geometryType() === 'LineString') {
                    $result[] = $this->reduceGeometry($curGeom);
                }
            }
        }

        if (count($result) === 0) {
            $this->errorMessage = 'Error: no lines in file.';
            return null;
        }
        return $result;
    }

    /**
     * Read the KML payload from a plain .kml file or a .kmz zip archive.
     * A KMZ holds the KML as a compressed member (usually doc.kml at the
     * archive root); the zip signature decides which path is taken, not
     * the uploaded file extension.
     */
    private function readKmlContent(string $path): ?string
    {
        // only the first bytes are needed for the zip signature check;
        // plain KML is read in full afterwards
        $head = file_get_contents($path, false, null, 0, 4);
        if ($head === false) {
            $this->errorMessage = 'Error: could not read file.';
            return null;
        }
        if (str_starts_with($head, "PK\x03\x04")) {
            return $this->extractFromKmz($path);
        }
        $kml = file_get_contents($path);
        if ($kml === false) {
            $this->errorMessage = 'Error: could not read file.';
            return null;
        }
        return $kml; // plain KML
    }

    private function extractFromKmz(string $path): ?string
    {
        if (!class_exists(\ZipArchive::class)) {
            $this->errorMessage = 'Error: could not read KMZ archive.';
            return null;
        }
        $zip = new \ZipArchive();
        if ($zip->open($path, \ZipArchive::CHECKCONS) !== true) {
            $this->errorMessage = 'Error: could not read KMZ archive.';
            return null;
        }

        // macOS archivers add AppleDouble junk (__MACOSX/._doc.kml) that
        // matches the .kml suffix but must not shadow the real document
        $kmlIndex = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === false || !preg_match('/\.kml$/i', $name)) {
                continue;
            }
            if (str_contains($name, '__MACOSX/') || str_starts_with(basename($name), '._')) {
                continue;
            }
            if (strcasecmp($name, 'doc.kml') === 0) {
                $kmlIndex = $i;
                break;
            }
            $kmlIndex ??= $i;
        }
        if ($kmlIndex === null) {
            $zip->close();
            $this->errorMessage = 'Error: no KML file in KMZ archive.';
            return null;
        }

        // cap the decompressed size: a small zip can inflate enormously
        $stat = $zip->statIndex($kmlIndex);
        if ($stat === false || $stat['size'] > self::MAX_KML_SIZE) {
            $zip->close();
            $this->errorMessage = 'Error: could not read KMZ archive.';
            return null;
        }

        $kml = $zip->getFromIndex($kmlIndex);
        $zip->close();
        if ($kml === false) {
            $this->errorMessage = 'Error: could not read KMZ archive.';
            return null;
        }
        return $kml;
    }

    private function reduceGeometry($curGeom): array
    {
        $tolerances = [
            ['tolerance' => 0.000004, 'fromZoom' => 16, 'toZoom' => 99],
            ['tolerance' => 0.000016, 'fromZoom' => 14, 'toZoom' => 16],
            ['tolerance' => 0.000064, 'fromZoom' => 12, 'toZoom' => 14],
            ['tolerance' => 0.000256, 'fromZoom' => 10, 'toZoom' => 12],
            ['tolerance' => 0.001024, 'fromZoom' => 8, 'toZoom' => 10],
            ['tolerance' => 0.004096, 'fromZoom' => 6, 'toZoom' => 8],
            ['tolerance' => 0.016384, 'fromZoom' => 4, 'toZoom' => 6],
        ];

        $origShape = new Shape();
        for ($j = 1; $j <= $curGeom->numPoints(); $j++) {
            $point = $curGeom->pointN($j);
            $origShape->addPoint(new ShapePoint((float)$point->coords[1], (float)$point->coords[0], $j));
        }

        $bBox = $curGeom->getBBox();
        $diagonal = sqrt(pow($bBox['maxx'] - $bBox['minx'], 2) + pow($bBox['maxy'] - $bBox['miny'], 2));

        $line = [
            'sw_lat' => $bBox['miny'],
            'sw_lng' => $bBox['minx'],
            'ne_lat' => $bBox['maxy'],
            'ne_lng' => $bBox['maxx'],
            'linedata' => [],
        ];

        $reducer = new ShapeReducer();
        foreach ($tolerances as $factor) {
            if ($factor['tolerance'] < (2 * $diagonal)) {
                $smallShape = $reducer->reduceWithTolerance($origShape, $factor['tolerance']);
                $line['linedata'][] = [
                    'fromZoom' => $factor['fromZoom'],
                    'toZoom' => $factor['toZoom'],
                    'data' => $this->shapeToJSON($smallShape, 2 + $factor['fromZoom'] / 2),
                ];
            }
        }
        return $line;
    }

    private function shapeToJSON(Shape $shape, int $digits): string
    {
        $json = '[';
        $sep = '';
        foreach ($shape->points() as $point) {
            $json .= $sep . '[' . substr((string)$point->lat, 0, (int)$digits) . ',' . substr((string)$point->lng, 0, (int)$digits) . ']';
            $sep = ',';
        }
        return $json . ']';
    }
}
