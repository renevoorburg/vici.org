<?php

namespace Vici\Model\Site\Edit;

use Vici\Identifiers\ExternalIdentifiers;
use Vici\Identifiers\IdentifierValidator;

/**
 * Validated data from the site edit form, following the same rules as
 * the legacy editor (title, summary, coordinates, year notation).
 */
class SiteFormData
{
    public ?int $id = null;
    public string $title = '';
    public string $summary = '';
    public string $annotation = '';
    public int $kind = 0;
    public float $lat = 0.0;
    public float $lng = 0.0;
    public int $isVisible = 1;
    public ?int $locationAccuracy = null;
    public ?int $startYear = null;
    public ?string $startYearStr = null;
    public ?int $endYear = null;
    public ?string $endYearStr = null;
    public ExternalIdentifiers $extIds;

    private array $errors = [];

    /** Kinds for which no start/end year is required (museum, event, findspot groups). */
    private const KINDS_WITHOUT_YEARS = [8, 18, 19];

    private function __construct() {}

    public static function fromPost(array $post, ?int $siteId): self
    {
        $data = new self();
        $data->id = $siteId;
        $normalized = [];
        foreach (self::postedExtIds($post) as $value) {
            $url = IdentifierValidator::normalize($value);
            if ($url === null) {
                $data->errors[] = 'Unrecognized external identifier: ' . $value;
            } else {
                $normalized[] = $url;
            }
        }
        $data->extIds = ExternalIdentifiers::withUrlsAndTagsArray($normalized);

        $data->setTitle($post['kop_edit'] ?? '');
        $data->setSummary($post['short_edit'] ?? '');
        $data->setAnnotation($post['edit_full'] ?? '');
        $data->kind = (int)($post['kindselect'] ?? 0);
        $data->setCoords($post['coords_frm'] ?? '');
        $data->isVisible = (int)($post['visibilityselect'] ?? 1);
        $accuracy = $post['accuracyselect'] ?? '';
        $data->locationAccuracy = ($accuracy === '' || $accuracy === null) ? null : (int)$accuracy;
        $data->setStartYear($post['start_yr'] ?? '');
        $data->setEndYear($post['end_yr'] ?? '');

        return $data;
    }

    private static function postedExtIds(array $post): array
    {
        $urls = [];
        for ($i = 0; $i <= 10; $i++) {
            $value = trim($post['extid' . $i] ?? '');
            if ($value !== '') {
                $urls[] = $value;
            }
        }
        return $urls;
    }

    public function isNew(): bool
    {
        return $this->id === null;
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorsHtml(): string
    {
        return implode('', array_map(fn($e) => 'Error: ' . htmlspecialchars($e, ENT_QUOTES, 'UTF-8') . '<br>', $this->errors));
    }

    public function getExtIdUrls(): array
    {
        return $this->extIds->getAllUrlsArray();
    }

    private function setTitle(string $title): void
    {
        $title = trim($title);
        $title = str_replace(['_', '/'], [' ', '-'], $title);
        if (mb_strlen($title) < 3) {
            $this->errors[] = 'Title should be at least 3 characters.';
        }
        $this->title = $title;
    }

    private function setSummary(string $summary): void
    {
        $summary = trim($summary);
        if (mb_strlen($summary) < 5) {
            $this->errors[] = 'Summary should be at least 5 characters.';
        }
        $this->summary = $summary;
    }

    private function setAnnotation(string $annotation): void
    {
        $this->annotation = trim($annotation);
    }

    private function setCoords(string $coordsStr): void
    {
        $coordsArr = array_map('trim', explode(',', $coordsStr));
        if (count($coordsArr) !== 2 || !is_numeric($coordsArr[0]) || !is_numeric($coordsArr[1])) {
            $this->errors[] = 'Coordinates out of range.';
            return;
        }
        $this->lat = (float)$coordsArr[0];
        $this->lng = (float)$coordsArr[1];
        if ($this->lat < -90 || $this->lat > 90 || $this->lng < -180 || $this->lng > 180) {
            $this->errors[] = 'Coordinates out of range.';
        }
    }

    private function setStartYear(string $year): void
    {
        $this->startYearStr = trim($year);

        // remove post/ante prefixes for numerical processing while keeping the original string
        $yeartmp = preg_replace('/^(post |ante )/', '', $this->startYearStr);

        // earliest variant: x -> 9 for BCE, x -> 0 for CE
        $yeartmp = (strlen($yeartmp) > 0 && $yeartmp[0] === '-')
            ? str_replace('x', '9', $yeartmp)
            : str_replace('x', '0', $yeartmp);

        $this->startYear = (int)preg_replace('/[?~]*/', '', $yeartmp);

        if ($this->startYear === 0 && !in_array($this->kind, self::KINDS_WITHOUT_YEARS, true)) {
            $this->errors[] = 'A valid (initial) year is required.';
        }
    }

    private function setEndYear(string $year): void
    {
        $year = trim($year);

        if (in_array($this->kind, self::KINDS_WITHOUT_YEARS, true)) {
            $this->endYearStr = '';
            $this->endYear = 9999;
            return;
        }

        if ($year === 'open') {
            $this->endYearStr = 'open';
            $this->endYear = 9999;
        } elseif ($year === 'unknown') {
            $this->endYearStr = 'unknown';
            $this->endYear = 0;
        } elseif ($year === '') {
            $this->errors[] = 'Please supply a valid end year.';
        } else {
            $this->endYearStr = $year;

            $yeartmp = preg_replace('/^(post |ante )/', '', $year);

            // latest variant: x -> 0 for BCE, x -> 9 for CE
            $yeartmp = ($yeartmp[0] === '-')
                ? str_replace('x', '0', $yeartmp)
                : str_replace('x', '9', $yeartmp);

            $this->endYear = (int)preg_replace('/[?~]*/', '', $yeartmp);

            if ($this->startYear !== null && $this->endYear < $this->startYear) {
                $this->errors[] = 'The end year cannot be before the initial year.';
            }
            if ($this->endYear === 0) {
                $this->errors[] = "Please supply a valid end year or use 'open' or 'unknown'.";
            }
        }
    }
}
