<?php

namespace Vici\Page\Api;

use Vici\Session\Session;
use Vici\Security\Altcha;
use Vici\Security\AccessControl;
use Vici\Model\User\Command\RegisterUserCommand;
use Vici\Model\User\Command\RegisterUserHandler;
use Vici\Model\User\UserRepository;

class UserApiController
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function postCreateUser(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        // Parse JSON body
        $raw = file_get_contents('php://input') ?: '';
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid JSON body']);
            return;
        }

        // Extract fields
        $accountName = $data['accountName'] ?? '';
        $realName = $data['realName'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $passwordConfirm = $data['passwordConfirm'] ?? '';
        $altchaPayload = $data['altcha'] ?? '';

        // Honeypot: hidden field humans never fill; a filled honeypot means a
        // bot — block the IP (escalating strikes) and refuse the submission
        if (trim((string)($data['website'] ?? '')) !== '') {
            AccessControl::blockIp($this->session->getIP());
            http_response_code(422);
            echo json_encode(['errors' => ['__all__' => 'user.bot_detected']]);
            return;
        }

        // ALTCHA verification; every POST needs a fresh, unreplayed solution
        $altcha = new Altcha($_ENV['ALTCHA_HMAC_KEY'] ?? '');
        if (!$altcha->validateOnce($altchaPayload)) {
            http_response_code(422);
            echo json_encode(['errors' => ['__all__' => 'user.bot_detected']]);
            return;
        }

        // Basic edge validation (server remains source of truth)
        $errors = [];   // Array of field => error message
        $accountNameTrim = trim((string)$accountName);
        $realNameTrim = trim((string)$realName);
        $emailTrim = strtolower(trim((string)$email));
        $passwordStr = (string)$password;
        $passwordConfirmStr = (string)$passwordConfirm;

        if (mb_strlen(preg_replace('/\s+/', '', $accountNameTrim)) < 4 || $accountNameTrim !== trim($accountNameTrim)) {
            $errors['accountName'] = 'user.account_min_length';
        }
        if (mb_strlen(preg_replace('/\s+/', ' ', $realNameTrim)) < 4 || $realNameTrim !== trim($realNameTrim)) {
            $errors['realName'] = 'user.realname_min_length';
        }
        if (!filter_var($emailTrim, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'user.email_invalid';
        }
        if (mb_strlen($passwordStr) < 8) {
            $errors['password'] = 'user.password_too_weak';
        }
        if ($passwordStr !== $passwordConfirmStr) {
            $errors['passwordConfirm'] = 'user.password_mismatch';
        }

        if ($errors) {
            http_response_code(422);
            echo json_encode(['errors' => $errors]);
            return;
        }

        // Hand over to domain handler
        $repo = new UserRepository($this->session->getDBConnector());
        $handler = new RegisterUserHandler($repo, $this->session);
        $cmd = new RegisterUserCommand(
            $accountNameTrim,
            preg_replace('/\s+/', ' ', $realNameTrim),
            $emailTrim,
            $passwordStr
        );

        try {
            $result = $handler($cmd); // expected array: ['id' => ..., 'createdAt' => ...]
            http_response_code(201);
            echo json_encode($result);
        } catch (\DomainException $e) {
            // Voorbeelden: uniqueness/conflict
            $payload = ['message' => $e->getMessage()];
            if (str_contains($e->getMessage(), 'account taken')) {
                $payload['errors'] = ['accountName' => 'user.account_taken'];
                http_response_code(409);
            } elseif (str_contains($e->getMessage(), 'email taken')) {
                $payload['errors'] = ['email' => 'user.email_taken'];
                http_response_code(409);
            } else {
                http_response_code(422);
            }
            echo json_encode($payload);
        } catch (\RuntimeException $e) {
            // Nog niet geïmplementeerd
            http_response_code(501);
            echo json_encode(['message' => 'Not implemented']);
        } catch (\Throwable $e) {
            error_log('UserApiController: ' . get_class($e) . ': ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['message' => 'Server error']);
        }
    }
}
