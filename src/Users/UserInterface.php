<?php

namespace Laasti\Warden\Users;

interface UserInterface
{
    public function getId();
    public function getIdentifier();
    public function getPasswordHash();
    public function getRoles();
    public function getPermissions();
}
