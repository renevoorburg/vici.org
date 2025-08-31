<?php

namespace Vici\Model\User\Command;

final class RegisterUserCommand
{
    public string $accountName;
    public string $realName;
    public string $email;
    public string $password;
    public string $ip;
    public string $userAgent;

    public function __construct(
        string $accountName,
        string $realName,
        string $email,
        string $password,
        string $ip,
        string $userAgent
    ) {
        $this->accountName = $accountName;
        $this->realName = $realName;
        $this->email = $email;
        $this->password = $password;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
    }
}
