<?php

namespace App\Security;

use App\Exception\InvalidTokenException;
use Lcobucci\JWT\Token;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Class TokenAuthenticator.
 */
class TokenAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var TokenManager
     */
    private $tokenManager;

    /**
     * TokenAuthenticator constructor.
     *
     * @param TokenManager $tokenManager
     */
    public function __construct(TokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new JsonResponse(
            ['error' => 'Auth header required'],
            401
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request): bool
    {
        return $request->headers->has('Authorization');
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidTokenException
     */
    public function getCredentials(Request $request)
    {
        $header = $request->headers->get('Authorization', '', true);

        $parts = explode(' ', $header);
        if (!(2 === \count($parts) && 0 === strcasecmp($parts[0], 'Bearer'))) {
            throw new InvalidTokenException('Invalid JWT token', 400);
        }

        $jwtToken = $this->tokenManager->parse($parts[1]);
        if (null === $jwtToken) {
            throw new InvalidTokenException('Invalid JWT token', 400);
        }

        $this->tokenManager->validate($jwtToken);

        return $jwtToken;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($token, UserProviderInterface $userProvider): ?UserInterface
    {
        if (!$token instanceof Token) {
            return null;
        }

        try {
            $userId = $token->getClaim('uid');
            $user = $userProvider->loadUserByUsername($userId);
        } catch (\Exception $exception) {
            $user = null;
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(['error' => 'Auth header invalid'], 401);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe(): bool
    {
        return false;
    }
}
