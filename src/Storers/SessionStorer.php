<?php

namespace SmallHadronCollider\SocialLogin\Storers;

use SmallHadronCollider\SocialLogin\Contracts\StorerInterface;

class SessionStorer implements StorerInterface
{
    protected $sessionID;

    public function __construct($sessionID = "social_login")
    {
        session_start();
        $this->sessionID = $sessionID;
    }

    public function store($id, $token)
    {
        $_SESSION["{$this->sessionID}.{$id}"] = $token;
        return $this;
    }

    public function get($id)
    {
        return $_SESSION["{$this->sessionID}.{$id}"];
    }

    public function clear($id)
    {
        unset($_SESSION["{$this->sessionID}.{$id}"]);
        return $this;
    }
}
