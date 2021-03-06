<?php

namespace SmallHadronCollider\SocialLogin\Platforms\One;

use League\OAuth1\Client\Server\Server;
use League\OAuth1\Client\Credentials\TokenCredentials;

use SmallHadronCollider\SocialLogin\Platforms\AbstractPlatform as Platform;
use SmallHadronCollider\SocialLogin\Contracts\PlatformInterface;
use SmallHadronCollider\SocialLogin\User;

use SmallHadronCollider\SocialLogin\Exceptions\InvalidAuthCodeException;
use SmallHadronCollider\SocialLogin\Exceptions\InvalidTokenException;

abstract class AbstractPlatform extends Platform implements PlatformInterface
{
    protected $server;

    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function getAuthUrl()
    {
        $this->checkSessionID();

        $temporaryIdentifier = $this->server->getTemporaryCredentials();
        $this->storer->store("{$this->platform}.{$this->sessionID}.temporary", serialize($temporaryIdentifier));
        return $this->server->getAuthorizationUrl($temporaryIdentifier);
    }

    public function getTokenFromCode($code)
    {
        $this->checkSessionID();

        $parts = explode(":", $code);

        if (count($parts) !== 2) {
            throw new InvalidAuthCodeException();
        }

        list($token, $verifier) = $parts;

        $key = "{$this->platform}.{$this->sessionID}.temporary";
        $temporaryCredentials = unserialize($this->storer->get($key));
        $tokenCredentials = $this->server->getTokenCredentials($temporaryCredentials, $token, $verifier);

        $this->storer->clear($key);
        return "{$tokenCredentials->getIdentifier()}:{$tokenCredentials->getSecret()}";
    }

    public function getUserFromToken($token)
    {
        $parts = explode(":", $token);

        if (count($parts) !== 2) {
            throw new InvalidTokenException();
        }

        list($identifier, $secret) = $parts;

        $tokenCredentials = new TokenCredentials();
        $tokenCredentials->setIdentifier($identifier);
        $tokenCredentials->setSecret($secret);

        return $this->createUser($tokenCredentials);
    }

    protected function createUser($tokenCredentials)
    {
        $user = new User();
        $user->setID($this->server->getUserUid($tokenCredentials));
        $user->setName($this->server->getUserScreenName($tokenCredentials));
        $user->setEmail($this->server->getUserEmail($tokenCredentials));
        $user->setProperties($this->server->getUserDetails($tokenCredentials)->extra);

        return $user;
    }
}
