<?php

namespace Vici\Model\User;

use Vici\DB\DBConnector;

class UserRepository
{
    private DBConnector $db;

    public function __construct(DBConnector $db)
    {
        $this->db = $db;
    }

    public function findById(int $id): ?User
    {
        $query = "SELECT * FROM accounts WHERE acc_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$userData) {
            return null;
        }
        
        $user = new User(
            $userData['acc_id'],
            $userData['acc_name'],
            $userData['acc_realname'],
            $userData['acc_email'],
            $userData['acc_passwd']
        );
        
        return $user;
    }
    
    public function authenticateUser(string $identity, string $password): ?User
    {
        $query = "SELECT * FROM accounts WHERE acc_name = :username OR acc_email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'username' => $identity,
            'email' => $identity
        ]);
        
        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$userData) {
            return null;
        }
        
        $user = new User(
            $userData['acc_id'],
            $userData['acc_name'],
            $userData['acc_realname'],
            $userData['acc_email'],
            $userData['acc_passwd']
        );
        
        if ($user->verifyPassword($password)) {
            return $user;
        }
        
        return null;
    }

    public function isAccountNameTaken(string $accountName): bool
    {
        $query = "SELECT COUNT(*) FROM accounts WHERE acc_name = :account_name";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'account_name' => $accountName
        ]);

        $count = $stmt->fetchColumn();
        return $count > 0;
    }

    public function isEmailTaken(string $emailLower): bool
    {
        $query = "SELECT COUNT(*) FROM accounts WHERE LOWER(acc_email) = :email";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'email' => $emailLower
        ]);

        $count = $stmt->fetchColumn();
        return $count > 0;
    }


    public function save(User $user): int
    {
        $query = "INSERT INTO accounts (acc_name, acc_realname, acc_email, acc_passwd, acc_level) VALUES (:account_name, :real_name, :email, :password, :level)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            'account_name' => $user->getName(),
            'real_name' => $user->getRealName(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'level' => $user->getLevel()
        ]);
        $user->setId((int)$this->db->lastInsertId());
        return $user->getId();
    }
}
