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

    public function __construct($id, $accountName, $realName, $email, $password = null)
    {
        $this->id = $id;
        $this->accountName = $accountName;
        $this->realName = $realName;
        $this->email = $email;
        $this->password = $password;
    }

    public function getId()
    {
        return $this->id;
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

    private function extractFirstName($realName)
    {
        $firstName = explode(' ', $realName)[0];
        $this->firstName = $firstName;
        return $firstName;
    }

    private function extractLastName($realName)
    {
        $lastName = explode(' ', $realName)[1];
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
