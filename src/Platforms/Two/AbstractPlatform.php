<?php

namespace SmallHadronCollider\SocialLogin\Platforms\Two;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;

use SmallHadronCollider\SocialLogin\Platforms\AbstractPlatform as Platform;
use SmallHadronCollider\SocialLogin\Contracts\PlatformInterface;
use SmallHadronCollider\SocialLogin\Exceptions\InvalidAuthCodeException;
use SmallHadronCollider\SocialLogin\User;

abstract class AbstractPlatform extends Platform implements PlatformInterface
{
    public $provider;

    public function __construct(AbstractProvider $provider)
    {
        $this->provider = $provider;
    }

    public function getAuthUrl()
    {
        $this->checkSessionID();

        $authURL = $this->provider->getAuthorizationUrl();
        $this->storer->store("{$this->platform}.{$this->sessionID}.temporary", $this->provider->getState());
        return $authURL;
    }

    public function getTokenFromCode($code)
    {
        $this->checkSessionID();

        list($code, $state) = explode(":", $code);

        // Get temporary credentials from storage
        $key = "{$this->platform}.{$this->sessionID}.temporary";
        $cachedState = $this->storer->get($key);

        if ($cachedState !== $state) {
            throw new InvalidAuthCodeException();
        }

        // Clear temporary credentials
        $this->storer->clear($key);

        $accessToken = $this->provider->getAccessToken("authorization_code", [
            "code" => $code,
        ]);

        return "{$accessToken->getToken()}";
    }

    public function getUserFromToken($token)
    {
        $accessToken = new AccessToken(["access_token" => $token]);
        return $this->createUser($accessToken);
    }

    protected function createUser($accessToken)
    {
        $resourceOwner = $this->provider->getResourceOwner($accessToken);

        $user = new User();
        $user->setID($this->getUserID($resourceOwner));
        $user->setName($this->getUserName($resourceOwner));
        $user->setEmail($this->getUserEmail($resourceOwner));
        $user->setProperties($resourceOwner->toArray());

        return $user;
    }

    abstract protected function getUserID($resourceOwner);
    abstract protected function getUserName($resourceOwner);
    abstract protected function getUserEmail($resourceOwner);
}
