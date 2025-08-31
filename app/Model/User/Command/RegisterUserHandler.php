<?php

namespace Vici\Model\User\Command;

use Vici\Model\User\UserRepository;
use Vici\Model\User\User;
use Vici\Session\Session;

final class RegisterUserHandler
{
    /** @var UserRepository */
    private $repo;
    /** @var Session */
    private $session;

    public function __construct(UserRepository $repo, Session $session)
    {
        $this->repo = $repo;
        $this->session = $session;
    }

    /**
     * @return array{id:int,createdAt:string}
     */
    public function __invoke(RegisterUserCommand $cmd): array
    {
        // Uniekheidschecks
        if ($this->repo->isAccountNameTaken($cmd->accountName)) {
            throw new \DomainException('account taken');
        }
        if ($this->repo->isEmailTaken($cmd->email)) {
            throw new \DomainException('email taken');
        }

        // Hash password
        $passwordHash = password_hash($cmd->password, PASSWORD_DEFAULT);

        // Maak domeinobject (User)
        $user = new User(
            0,
            $cmd->accountName,
            $cmd->realName,
            $cmd->email,
            $passwordHash
        );

 
        $newId = $this->repo->save($user);

        $createdAt = gmdate('c');
        return ['id' => $newId, 'createdAt' => $createdAt];
    }
}

