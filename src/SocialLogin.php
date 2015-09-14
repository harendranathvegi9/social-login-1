<?php

namespace SmallHadronCollider\SocialLogin;

use SmallHadronCollider\SocialLogin\Platforms\One;
use SmallHadronCollider\SocialLogin\Platforms\Two;
use SmallHadronCollider\SocialLogin\Exceptions\InvalidPlatformException;
use SmallHadronCollider\SocialLogin\Contracts\StorerInterface;

class SocialLogin
{
    private $platforms = [];

    public function __construct(array $config, StorerInterface $storer)
    {
        if (array_key_exists("facebook", $config)) {
            $this->platforms["facebook"] = $this->getFacebookPlatform($config["facebook"])->setStorer($storer);
        }

        if (array_key_exists("twitter", $config)) {
            $this->platforms["twitter"] = $this->getTwitterPlatform($config["twitter"])->setStorer($storer);
        }
    }

    public function platform($platform)
    {
        if (array_key_exists($platform, $this->platforms)) {
            return $this->platforms[$platform];
        }

        throw new InvalidPlatformException($platform);
    }

    public function getAuthUrls()
    {
        return array_map(function ($platform) {
            return $platform->getAuthUrl();
        }, $this->platforms);
    }

    private function getFacebookPlatform($config)
    {
        return new Two\FacebookPlatform($config);
    }

    private function getTwitterPlatform($config)
    {
        return new One\TwitterPlatform($config);
    }
}