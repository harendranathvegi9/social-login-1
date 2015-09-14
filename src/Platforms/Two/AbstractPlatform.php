<?php

namespace SmallHadronCollider\SocialLogin\Platforms\Two;

use SmallHadronCollider\SocialLogin\Platforms\AbstractPlatform as Platform;
use SmallHadronCollider\SocialLogin\Contract\PlatformInterface;
use SmallHadronCollider\SocialLogin\User;

abstract class AbstractPlatform extends Platform implements PlatformInterface
{
    protected $provider;

    public function getAuthUrl()
    {
        return $this->provider->getAuthorizationUrl();
    }

    public function setAuthCode($code)
    {
        $accessToken = $this->provider->getAccessToken("authorization_code", [
            "code" => $code,
        ]);

        $this->storeAccessToken($accessToken);
    }

    public function getUser($userID)
    {
        $accessToken = unserialize($this->storer->get("{$this->platform}.{$userID}"));
        $resourceOwner = $this->provider->getResourceOwner($accessToken);

        $user = new User();
        $user->setID($this->getUserID($resourceOwner));
        $user->setName($this->getUserName($resourceOwner));
        $user->setEmail($this->getUserEmail($resourceOwner));
        $user->setProperties($resourceOwner->toArray());

        return $user;
    }

    protected function storeAccessToken($accessToken)
    {
        $resourceOwner = $this->provider->getResourceOwner($accessToken);
        $userID = $this->getUserID($resourceOwner);
        $this->storer->store("{$this->platform}.{$userID}", serialize($accessToken));
    }

    abstract protected function getUserID($resourceOwner);
    abstract protected function getUserName($resourceOwner);
    abstract protected function getUserEmail($resourceOwner);
}