<?php

namespace Vici\Security;

use AltchaOrg\Altcha\Altcha as AltchaLib;
use AltchaOrg\Altcha\Algorithm\Sha;
use AltchaOrg\Altcha\CreateChallengeOptions;
use AltchaOrg\Altcha\VerifySolutionOptions;

/**
 * Class voor het afhandelen van ALTCHA proof-of-work captcha functionaliteit
 */
class Altcha
{
    private AltchaLib $altcha;
    private Sha $algorithm;

    /**
     * Constructor
     *
     * @param string $secretKey De HMAC sleutel waarmee challenges ondertekend worden
     */
    public function __construct(string $secretKey)
    {
        if ($secretKey === '') {
            throw new \RuntimeException('ALTCHA_HMAC_KEY is not configured');
        }
        $this->altcha = new AltchaLib($secretKey);
        $this->algorithm = new Sha();
    }

    /**
     * Maak een nieuwe challenge voor de widget
     *
     * @return string De challenge als JSON
     */
    public function createChallenge(): string
    {
        return $this->altcha->createChallenge(new CreateChallengeOptions(
            algorithm: $this->algorithm,
            cost: 500,
            counter: random_int(500, 3000),
            expiresAt: time() + 600,
        ))->toJson();
    }

    /**
     * Valideer een ALTCHA payload
     *
     * @param string $payload De base64 payload zoals de widget die in het formulier zet
     * @return bool True als validatie slaagt, anders false
     */
    public function validate(string $payload): bool
    {
        if ($payload === '') {
            return false;
        }
        try {
            return $this->altcha->verifySolution(new VerifySolutionOptions(
                payload: $payload,
                algorithm: $this->algorithm,
            ))->verified;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Valideer een payload en blokkeer hergebruik (replay).
     *
     * Een opgeloste challenge blijft tot 600s geldig; zonder replay-guard kan
     * dezelfde payload meerdere formulieren/posts autoriseren. apcu_add() is
     * atomair: slechts één request kan een payload-key claimen.
     *
     * @param string $payload De base64 payload zoals de widget die in het formulier zet
     * @return bool True als de payload valide én nog niet eerder gebruikt is
     */
    public function validateOnce(string $payload): bool
    {
        if ($payload === '') {
            return false;
        }
        if (!function_exists('apcu_add')) {
            return $this->validate($payload);
        }
        $replayKey = 'altcha_used_' . hash('sha256', $payload);
        if (!apcu_add($replayKey, true, 660)) {
            return false;
        }
        if (!$this->validate($payload)) {
            apcu_delete($replayKey);
            return false;
        }
        return true;
    }
}
