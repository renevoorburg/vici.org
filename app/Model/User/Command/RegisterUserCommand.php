<?php

namespace Vici\Model\User\Command;

final class RegisterUserCommand
{
    public string $accountName;
    public string $realName;
    public string $email;
    public string $password;
    
    public function __construct(
        string $accountName,
        string $realName,
        string $email,
        string $password
    ) {
        $this->accountName = $accountName;
        $this->realName = $realName;
        $this->email = $email;
        $this->password = $password;
    }
}
