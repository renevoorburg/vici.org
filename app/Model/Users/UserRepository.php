<?php

namespace Vici\Model\Users;

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
        $query = "SELECT * FROM accounts WHERE acc_id = :id";
        $stmt = $this->dbConnector->prepare($query);
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetchObject(User::class);
        return $result;
    }
    
    public function authenticateUser(string $identity, string $password): ?User
    {
        $query = "SELECT * FROM accounts WHERE acc_name = :username OR acc_email = :email";
        $stmt = $this->dbConnector->prepare($query);
        $stmt->execute([
            'username' => $identity,
            'email' => $identity
        ]);
        
        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$userData) {
            return null; // Gebruiker niet gevonden
        }
        
        // Maak User object
        $user = new User(
            $userData['acc_id'],
            $userData['acc_name'],
            $userData['acc_realname'],
            $userData['acc_email'],
            $userData['acc_passwd']
        );
        
        // Controleer wachtwoord
        if ($user->verifyPassword($password)) {
            return $user;
        }
        
        return null; // Wachtwoord onjuist
    }
}
