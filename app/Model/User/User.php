<?php

namespace Vici\Model\User;

class User
{
    private int $id;
    private string $accountName;
    private string $realName;
    private ?string $firstName = null;
    private ?string $lastName = null;
    private ?string $initials = null;
    private string $email;
    private string $password;
    private $level;

    const LEVEL_DELETED = 0;        // deleted: record kept for integrity, acc. data flushed
    const LEVEL_LOCKED = 1;         // locked: can do nothing until situation cleared
    const LEVEL_NOT_CONFIRMED = 2;  // not confirmed: new account, waits for confirmation
    const LEVEL_CONFIRMED = 3;      // confirmed, not trusted (postings need to be checked)
    const LEVEL_TRUSTED = 4;        // trusted
    const LEVEL_ADMIN = 5;          // admin  

    public function __construct($id, $accountName, $realName, $email, $password = null)
    {
        $this->id = $id;
        $this->accountName = $accountName;
        $this->realName = $realName;
        $this->email = $email;
        $this->password = $password;
        $this->level = self::LEVEL_NOT_CONFIRMED;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        if ($this->id > 0) {
            throw new \LogicException('Cannot set ID for existing user');
        }
        if ($id < 1) {
            throw new \InvalidArgumentException('Invalid ID');
        }
        $this->id = $id;
        return $this;
    }

    public function getName()
    {
        return $this->accountName;
    }

    public function getRealName()
    {
        return $this->realName;
    }

    public function getFirstName()
    {
        if ($this->firstName) {
            return $this->firstName;
        }
        return $this->extractFirstName($this->realName);
    }

    public function getLastName()
    {
        if ($this->lastName) {
            return $this->lastName;
        }
        return $this->extractLastName($this->realName);
    }

    public function getInitials()
    {
        if ($this->initials) {
            return $this->initials;
        }
        return $this->extractInitials($this->realName);
    }

    public function getEmail()
    {
        return $this->email;
    }
    
    public function getPassword()
    {
        return $this->password;
    }
    
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
    
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    public function getLevel()
    {
        return $this->level;
    }   

    public function setLevel($level)
    {
        $this->level = $level;
        return $this;
    }

    public function isDeleted()
    {
        return $this->level === self::LEVEL_DELETED;
    }

    public function isLocked()
    {
        return $this->level === self::LEVEL_LOCKED;
    }

    public function isNotConfirmed()
    {
        return $this->level === self::LEVEL_NOT_CONFIRMED;
    }

    public function isConfirmed()
    {
        return $this->level >= self::LEVEL_CONFIRMED;
    }

    public function isTrusted()
    {
        return $this->level >= self::LEVEL_TRUSTED;
    }

    public function isAdmin()
    {
        return $this->level === self::LEVEL_ADMIN;
    }

    public function __toString()
    {
        return $this->accountName;
    }

    private function extractFirstName($realName)
    {
        $parts = explode(' ', $realName);
        $firstName = $parts[0] ?? '';
        $this->firstName = $firstName;
        return $firstName;
    }

    private function extractLastName($realName)
    {
        $parts = explode(' ', $realName);
        $lastName = $parts[1] ?? '';
        $this->lastName = $lastName;
        return $lastName;
    }

    private function extractInitials($realName)
    {
        $firstName = $this->firstName;
        if (!$firstName) {
            $firstName = $this->extractFirstName($realName);
        }
        $words = preg_split('/\s+/', trim($firstName));
        $initials = '';
        foreach ($words as $word) {
            if ($word !== '') {
                $initials .= mb_strtoupper(mb_substr($word, 0, 1)) . '.';
            }
        }
        $this->initials = $initials;
        return $initials;
    }   
}
