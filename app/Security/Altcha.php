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
}
