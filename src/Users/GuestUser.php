<?php

namespace Laasti\Warden\Users;

class GuestUser implements UserInterface
{

    public function getId()
    {
        return 0;
    }

    public function getIdentifier()
    {
        return null;
    }

    public function getPasswordHash()
    {
        return null;
    }
    public function setPasswordHash()
    {
        return $this;
    }

    public function getRoles()
    {
        return [];
    }

    public function getPermissions()
    {
        return [];
    }
}
