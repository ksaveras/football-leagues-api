<?php

namespace App\Security;

use App\Exception\InvalidTokenException;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\ValidationData;

/**
 * Class TokenManager.
 */
class TokenManager
{
    /**
     * @var string
     */
    private $issuer = 'localhost';

    /**
     * @var int
     */
    private $ttl = 3600;

    /**
     * @var Signer
     */
    private $signer;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * TokenManager constructor.
     *
     * @param Signer $signer
     * @param string $secretKey
     */
    public function __construct(Signer $signer, string $secretKey)
    {
        $this->signer = $signer;
        $this->secretKey = $secretKey;
    }

    /**
     * @return string
     */
    public function getIssuer(): string
    {
        return $this->issuer;
    }

    /**
     * @param string $issuer
     *
     * @return $this
     */
    public function setIssuer(string $issuer): self
    {
        $this->issuer = $issuer;

        return $this;
    }

    /**
     * @return int
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }

    /**
     * @param int $ttl
     *
     * @return $this
     */
    public function setTtl(int $ttl): self
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * @param string $uid
     *
     * @return Token
     */
    public function create(string $uid): Token
    {
        $jwtToken = (new Builder())
            ->setIssuer($this->getIssuer())
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration(time() + $this->getTtl())
            ->set('uid', $uid)
            ->sign($this->signer, $this->secretKey)
            ->getToken();

        return $jwtToken;
    }

    /**
     * @param Token $token
     *
     * @return bool
     *
     * @throws InvalidTokenException
     */
    public function validate(Token $token): bool
    {
        $data = new ValidationData();
        $data->setIssuer($this->getIssuer());
        if (!$token->validate($data)) {
            throw new InvalidTokenException('Invalid JWT token', 400);
        }

        if (!$token->verify($this->signer, $this->secretKey)) {
            throw new InvalidTokenException('Invalid JWT token', 400);
        }

        return true;
    }

    /**
     * @param string $value
     *
     * @return Token|null
     */
    public function parse(string $value): ?Token
    {
        try {
            $token = (new Parser())->parse($value);
        } catch (\Exception $exception) {
            $token = null;
        }

        return $token;
    }
}
