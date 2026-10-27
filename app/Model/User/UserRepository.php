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

    private function hydrate(array $userData): User
    {
        $user = new User(
            $userData['acc_id'],
            $userData['acc_name'],
            $userData['acc_realname'],
            $userData['acc_email'],
            $userData['acc_passwd']
        );
        $user->setLevel((int)$userData['acc_level']);
        return $user;
    }

    public function findById(int $id): ?User
    {
        $query = "SELECT * FROM accounts WHERE acc_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['id' => $id]);
        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $userData ? $this->hydrate($userData) : null;
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

        $user = $this->hydrate($userData);

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

    private static function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Store a fresh confirmation code for a new account and return it.
     */
    public function createConfirmationCode(int $accountId): string
    {
        $code = self::uuid();
        $stmt = $this->db->prepare("INSERT INTO unconfirmedaccs (uconf_acc_id, conf_code) VALUES (:id, :code)");
        $stmt->execute(['id' => $accountId, 'code' => $code]);

        return $code;
    }

    public function findAccountIdByConfirmationCode(string $code): ?int
    {
        $stmt = $this->db->prepare("SELECT uconf_acc_id FROM unconfirmedaccs WHERE conf_code = :code");
        $stmt->execute(['code' => $code]);
        $id = $stmt->fetchColumn();
        return $id === false ? null : (int)$id;
    }

    /**
     * Activate a not-yet-confirmed account and spend the confirmation code.
     * Returns false when the account was already activated.
     */
    public function activateAccount(int $accountId): bool
    {
        $stmt = $this->db->prepare("UPDATE accounts SET acc_level = :confirmed WHERE acc_id = :id AND acc_level <= :unconfirmed");
        $stmt->execute([
            'confirmed' => User::LEVEL_CONFIRMED,
            'unconfirmed' => User::LEVEL_NOT_CONFIRMED,
            'id' => $accountId
        ]);
        $this->db->prepare("DELETE FROM unconfirmedaccs WHERE uconf_acc_id = :id")
            ->execute(['id' => $accountId]);
        return $stmt->rowCount() > 0;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM accounts WHERE acc_email = :email");
        $stmt->execute(['email' => $email]);
        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $userData ? $this->hydrate($userData) : null;
    }

    /**
     * Store a fresh password-reset code for an account and return it.
     * A second request replaces the previous code.
     */
    public function createPasswordResetCode(int $accountId): string
    {
        $code = self::uuid();
        $stmt = $this->db->prepare("INSERT INTO resetpw (reset_acc_id, conf_code) VALUES (:id, :code) ON DUPLICATE KEY UPDATE conf_code = :code2");
        $stmt->execute(['id' => $accountId, 'code' => $code, 'code2' => $code]);
        return $code;
    }

    public function findAccountByResetCode(string $code): ?User
    {
        $stmt = $this->db->prepare("SELECT accounts.* FROM resetpw JOIN accounts ON reset_acc_id = acc_id WHERE conf_code = :code");
        $stmt->execute(['code' => $code]);
        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $userData ? $this->hydrate($userData) : null;
    }

    /**
     * Set a new password and spend the reset code. A not-yet-confirmed
     * account is activated by the reset (as on the old site).
     */
    public function resetPassword(int $accountId, string $password): void
    {
        $this->db->prepare("UPDATE accounts SET acc_passwd = :hash WHERE acc_id = :id")
            ->execute(['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $accountId]);
        $this->db->prepare("DELETE FROM resetpw WHERE reset_acc_id = :id")
            ->execute(['id' => $accountId]);
        $this->db->prepare("UPDATE accounts SET acc_level = :confirmed WHERE acc_id = :id AND acc_level <= :unconfirmed")
            ->execute(['confirmed' => User::LEVEL_CONFIRMED, 'unconfirmed' => User::LEVEL_NOT_CONFIRMED, 'id' => $accountId]);
        $this->db->prepare("DELETE FROM unconfirmedaccs WHERE uconf_acc_id = :id")
            ->execute(['id' => $accountId]);
    }
}
