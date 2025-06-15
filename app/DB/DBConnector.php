<?php

namespace Vici\DB;

use mysqli;

class DBConnector extends mysqli
{

    public function __construct($database = 'MAIN')
    {
        $datahost = $_ENV['DB_HOST'] ?? '';
        $database = $_ENV['DB_' . $database] ?? '';
        $username = $_ENV['DB_USER'] ?? '';
        $password = $_ENV['DB_PASS'] ?? '';

        parent::__construct($datahost, $username, $password, $database);
        parent::set_charset("utf8");
    }

}