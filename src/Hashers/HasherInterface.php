<?php

namespace Laasti\Warden\Hashers;

interface HasherInterface
{
    /**
     * Hash the password
     * @param string $password
     * @return string
     */
    public function hash($password);

    /**
     * Check password against hash
     * @param string $password
     * @param string $hash
     * @return boolean
     */
    public function verify($password, $hash);
}
