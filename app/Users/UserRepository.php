<?php

namespace Vici\Users;

use Vici\DB\DBConnector;

class UserRepository
{
    private DBConnector $dbConnector;

    public function __construct(DBConnector $dbConnector)
    {
        $this->dbConnector = $dbConnector;
    }

    public function getUserById(int $id): ?User
    {
        $query = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->dbConnector->prepare($query);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetchObject(User::class);
        return $result;
    }
}
