<?php

namespace Laasti\Warden\Users;

/**
 * PdoUser Class
 *
 */
class PdoUser implements UserInterface
{

    protected $id;
    protected $email;
    protected $password;
    protected $roles = [];
    protected $permissions = [];

    public function __construct($properties)
    {
        foreach ($properties as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getIdentifier()
    {
        return $this->email;
    }

    public function getPasswordHash()
    {
        return $this->password;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }
}
