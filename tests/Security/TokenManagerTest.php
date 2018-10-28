<?php

namespace App\Tests\Security;

use App\Security\TokenManager;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;
use PHPUnit\Framework\TestCase;

/**
 * Class TokenManagerTest.
 */
class TokenManagerTest extends TestCase
{
    public function testCreate(): void
    {
        $manager = $this->createTokenManager();
        $token = $manager->create('123');

        $this->assertEquals('localhost', $token->getClaim('iss'));
        $this->assertEquals('123', $token->getClaim('uid'));
    }

    public function testValidate(): void
    {
        $manager = $this->createTokenManager();
        $token = $manager->create('123');

        $result = $manager->validate($token);

        $this->assertTrue($result);
    }

    /**
     * @expectedException \App\Exception\InvalidTokenException
     * @expectedExceptionMessage Invalid JWT token
     */
    public function testValidateExceptions(): void
    {
        $manager = $this->createTokenManager();
        $token = $manager->create('123');

        $manager->setIssuer('altered');
        $result = $manager->validate($token);

        $this->assertTrue($result);
    }

    public function testParse(): void
    {
        $manager = $this->createTokenManager();
        $jwtToken = $manager->parse(
            'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9leGFtcGxlLmNvbSIsImF1ZCI6Im' .
            'h0dHA6XC9cL2V4YW1wbGUub3JnIiwiaWF0IjoxNTQwNjUyNjI0LCJuYmYiOjE1NDA2NTI2ODQsImV4cCI6MTU0MDY1NjI' .
            'yNCwidWlkIjoiYWRtaW4ifQ.HMTq4sFP43hu4zWdZzhMfCz353LT-PBosKGjYZWoxbA'
        );

        $this->assertInstanceOf(Token::class, $jwtToken);
    }

    public function testParseInvalidToken(): void
    {
        $manager = $this->createTokenManager();
        $jwtToken = $manager->parse('');

        $this->assertNull($jwtToken);
    }

    /**
     * @return TokenManager
     */
    private function createTokenManager(): TokenManager
    {
        $manager = new TokenManager(new Sha256(), '123');
        $manager->setTtl(300);

        return $manager;
    }
}
