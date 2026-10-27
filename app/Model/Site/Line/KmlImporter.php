<?php

namespace Vici\Model\Site\Line;

use Vici\DB\DBConnector;
use Vici\Geo\Shape;
use Vici\Geo\ShapePoint;
use Vici\Geo\ShapeReducer;
use geoPHP;

class KmlImporter
{
    private string $errorMessage = '';

    /**
     * Replace all lines of a site with the LineStrings found in a KML file.
     * Existing lines are hidden (line_hide=1), new lines are stored with
     * simplified geometry per zoom band in pline_data.
     *
     * @return bool true on success, false on error (see getErrorMessage())
     */
    public function replaceLinesForSite(string $kmlPath, int $siteId, int $userId, int $lineKind, int $license, string $owner, string $attribution): bool
    {
        $lines = $this->reduceFile($kmlPath);
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
        $file = file_get_contents($path);
        if ($file === false) {
            $this->errorMessage = 'Error: could not read file.';
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
