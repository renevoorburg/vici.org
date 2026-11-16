<?php

namespace Vici\Page\Api;

use Vici\Identifiers\IdentifierValidator;

class ExtIdController
{
    public function check(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $value = $_GET['value'] ?? '';
        if (!is_string($value) || strlen($value) > 2048) {
            http_response_code(400);
            echo json_encode(['error' => 'invalid or missing value']);
            return;
        }
        $url = IdentifierValidator::normalize($value);

        echo json_encode([
            'valid' => $url !== null,
            'url' => $url,
        ]);
    }
}
