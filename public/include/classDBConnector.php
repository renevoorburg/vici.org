<?php

require_once __DIR__ . '/classViciCommon.php';

class DBConnector extends mysqli
{
    const DEBUG = false;

    private string $database;
    private array $primaryKeysArr ;

    public function __construct($connectTo = 'main')
    {
// db settings for testing, main db:
        $datahost = getenv('DB_HOST');
        $database = getenv('DB_NAME');
        $username = getenv('DB_USER');
        $password = getenv('DB_PASS');

        parent::__construct($datahost, $username, $password, $database);
        parent::set_charset("utf8");

        $this->database = $database;
    }

    public function query($query, $result_mode = NULL)
    {
        if (self::DEBUG) {
            print "<p>$query</p>\n";
        }
        return parent::query($query, $result_mode);
    }

    private function getTableNullableColums(string $table) : array
    {
        $ret = [];
        $result = $this->query(
            "SELECT column_name
                    FROM information_schema.columns
                    WHERE table_schema='" . $this->database . "'
                    AND table_name='" . $table . "' AND is_nullable='YES'"
        );
        if ($result->num_rows > 0 ) {
            while ($resArr = $result->fetch_array()) {
                $ret[] = $resArr[0];
            }
        }
        return $ret;
    }

    public function insertUpdate(string $table, array $valuesArr, bool $nullifyEmpties = true)
    {
        if ($nullifyEmpties) {
            $nullableColsArr = $this->getTableNullableColums($table);
        }
        $keysList = '';
        $valuesList = '';
        $updatesList = '';
        $sep = '';
        $i = 1;
        foreach ($valuesArr as $key => $value) {

            // treat empty fields as NULL when column IS_NULLABLE:
            if ($nullifyEmpties && in_array($key, $nullableColsArr) && empty($value)) {
                $value = null;
            }

            // proper escaping and quoting:
            if (gettype($value) == 'string') {
                $value = "'" . parent::real_escape_string($value) . "'";
            } elseif (gettype($value) == 'double') {
                $value = "'" . $value . "'";
            } elseif (gettype($value) == 'boolean') {
                $value = $value ? 1 : 0 ;
            } elseif (is_null($value)) {
                $value = 'NULL';
            }

            $valuesList .= $sep . $value;
            $keysList .= $sep . $key ;
            $updatesList .= $sep . $key . '=' . $value;
            $sep = ', ';
            $i++;
        }

        $sql = 'INSERT IGNORE INTO ' . $table . ' (' . $keysList . ') ';
        $sql .= 'VALUES (' . $valuesList . ') ';
        $sql .= 'ON DUPLICATE KEY UPDATE ' . $updatesList;
        return $this->query($sql);
    }

}
