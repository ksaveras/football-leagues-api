<?php

namespace App\Tests\Security;

use App\Security\TokenAuthenticator;
use App\Security\TokenManager;
use Lcobucci\JWT\Token;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class TokenAuthenticatorTest.
 */
class TokenAuthenticatorTest extends TestCase
{
    public function testGetCredentials(): void
    {
        $request = Request::create(
            '/',
            'GET',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer 123',
            ]
        );

        $token = $this->createMock(Token::class);

        $manager = $this->getTokenManager();
        $manager->expects($this->once())
            ->method('parse')
            ->with('123')
            ->willReturn($token);

        $authenticator = new TokenAuthenticator($manager);
        $credentials = $authenticator->getCredentials($request);

        $this->assertEquals($token, $credentials);
    }

    /**
     * @dataProvider             invalidCredentialsProvider
     * @expectedException \App\Exception\InvalidTokenException
     * @expectedExceptionMessage Invalid JWT token
     *
     * @param Request $request
     */
    public function testGetInvalidCredentials(Request $request): void
    {
        $manager = $this->getTokenManager();

        $authenticator = new TokenAuthenticator($manager);
        $authenticator->getCredentials($request);
    }

    public function testCheckCredentials(): void
    {
        $authenticator = new TokenAuthenticator($this->getTokenManager());
        $credentials = '';
        $user = $this->createMock(UserInterface::class);

        $this->assertTrue($authenticator->checkCredentials($credentials, $user));
    }

    public function testOnAuthenticationSuccess(): void
    {
        $request = $this->createMock(Request::class);
        $token = $this->createMock(TokenInterface::class);
        $providerKey = 'demo';

        $authenticator = new TokenAuthenticator($this->getTokenManager());
        $response = $authenticator->onAuthenticationSuccess($request, $token, $providerKey);

        $this->assertNull($response);
    }

    public function testOnAuthenticationFailure(): void
    {
        $request = $this->createMock(Request::class);
        $exception = $this->createMock(AuthenticationException::class);

        $authenticator = new TokenAuthenticator($this->getTokenManager());
        $response = $authenticator->onAuthenticationFailure($request, $exception);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals('{"error":"Auth header invalid"}', $response->getContent());
    }

    public function testSupportsRememberMe(): void
    {
        $authenticator = new TokenAuthenticator($this->getTokenManager());

        $this->assertFalse($authenticator->supportsRememberMe());
    }

    public function testGetUserInvalidToken(): void
    {
        $userProvider = $this->createMock(UserProviderInterface::class);

        $authenticator = new TokenAuthenticator($this->getTokenManager());
        $user = $authenticator->getUser(null, $userProvider);

        $this->assertNull($user);
    }

    public function testGetUser(): void
    {
        $userMock = $this->createMock(UserInterface::class);

        $userProvider = $this->createMock(UserProviderInterface::class);
        $userProvider->expects($this->once())
            ->method('loadUserByUsername')
            ->with('userId')
            ->willReturn($userMock);

        $jwtToken = $this->createMock(Token::class);
        $jwtToken->method('getClaim')
            ->with('uid')
            ->willReturn('userId');

        $authenticator = new TokenAuthenticator($this->getTokenManager());
        $user = $authenticator->getUser($jwtToken, $userProvider);

        $this->assertSame($userMock, $user);
    }

    public function testGetUnknownUser(): void
    {
        $userProvider = $this->createMock(UserProviderInterface::class);
        $userProvider->expects($this->once())
            ->method('loadUserByUsername')
            ->with('userId')
            ->willThrowException(new UsernameNotFoundException());

        $jwtToken = $this->createMock(Token::class);
        $jwtToken->method('getClaim')
            ->with('uid')
            ->willReturn('userId');

        $authenticator = new TokenAuthenticator($this->getTokenManager());
        $user = $authenticator->getUser($jwtToken, $userProvider);

        $this->assertNull($user);
    }

    public function testStart(): void
    {
        $request = $this->createMock(Request::class);

        $authenticator = new TokenAuthenticator($this->getTokenManager());
        $response = $authenticator->start($request);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals('{"error":"Auth header required"}', $response->getContent());
    }

    /**
     * @dataProvider authenticatorSupportsProvider
     *
     * @param Request $request
     */
    public function testSupports(Request $request): void
    {
        $authenticator = new TokenAuthenticator($this->getTokenManager());

        $this->assertTrue($authenticator->supports($request));
    }

    /**
     * @return \Generator
     */
    public function authenticatorSupportsProvider(): \Generator
    {
        $request = Request::create(
            '/',
            'GET',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer 123',
            ]
        );

        yield [$request];

        $request = Request::create(
            '/',
            'GET',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => '',
            ]
        );

        yield [$request];
    }

    /**
     * @return \Generator
     */
    public function invalidCredentialsProvider(): ?\Generator
    {
        $request = Request::create(
            '/',
            'GET',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer',
            ]
        );

        yield [$request];

        $request = Request::create(
            '/',
            'GET',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer 123',
            ]
        );

        yield [$request];
    }

    /**
     * @return TokenManager|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getTokenManager()
    {
        return $this->createMock(TokenManager::class);
    }
}
