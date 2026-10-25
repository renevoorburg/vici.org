<?php

namespace Vici\API;

use Vici\Session\Session;

abstract class APICall
{
    public function __construct(Session $session)
    {
    }

    abstract public function payload();

    public function headers()
    {
        header("Content-Type: application/json");
        header("Cache-Control: public, max-age=300");
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + 300) . " GMT");
    }

    // CORS (incl. OPTIONS preflight) is handled by the fronting nginx,
    // which is the single entry point for the app.

    public function get()
    {
        ob_start();
        $this->headers();
        $this->payload();
        ob_end_flush();
    }


}
