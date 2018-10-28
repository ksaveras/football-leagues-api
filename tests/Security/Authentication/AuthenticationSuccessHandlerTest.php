<?php

namespace App\Tests\Security\Authentication;

use App\Security\Authentication\AuthenticationSuccessHandler;
use App\Security\TokenManager;
use Lcobucci\JWT\Token;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class AuthenticationSuccessHandlerTest.
 */
class AuthenticationSuccessHandlerTest extends TestCase
{
    public function testOnAuthenticationSuccess(): void
    {
        $username = 'SuperUser';

        $jwtToken = $this->createMock(Token::class);
        $jwtToken->method('__toString')
            ->willReturn('super token');

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUsername')
            ->willReturn($username);

        $manager = $this->createMock(TokenManager::class);
        $manager->method('create')
            ->with($username)
            ->willReturn($jwtToken);

        $request = $this->createMock(Request::class);

        $handler = new AuthenticationSuccessHandler($manager);
        $response = $handler->onAuthenticationSuccess($request, $token);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
        $this->assertEquals('{"token":"super token"}', $response->getContent());
    }
}
