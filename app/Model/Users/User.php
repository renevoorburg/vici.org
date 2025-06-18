<?php

namespace Vici\Model\Users;

class User
{
    private $id;
    private $accountName;
    private $realName;
    private $email;
    private $password;
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
}
