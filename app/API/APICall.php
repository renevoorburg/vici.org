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

    private function corsHeaders()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: X-Requested-With, X-Vici-Token');
    }

    private function preflight()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'GET') {
              $this->corsHeaders();
            }
            exit;
          } 
    }

    public function get()
    {
        $this->preflight();

        ob_start();
        $this->corsHeaders();
        $this->headers();
        $this->payload();
        ob_end_flush();
    }


}
