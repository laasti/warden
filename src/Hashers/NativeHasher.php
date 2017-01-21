<?php

namespace Laasti\Warden\Hashers;

class NativeHasher implements HasherInterface
{
    /**
     * Algorithm to use for passwords
     * @var int
     */
    protected $algorithm;

    /**
     * Options for the current algorithm
     * @var array
     */
    protected $options;

    public function __construct($algorithm = PASSWORD_DEFAULT, $options = [])
    {
        $this->algorithm = $algorithm;
        $this->options = $options;
    }

    /**
     * Returns a hashed password ready for storage
     * @param string $password
     * @return string
     */
    public function hash($password)
    {
        return password_hash($password, $this->algorithm, $this->options);
    }

    /**
     * Check a password against a hash
     * @param string $password
     * @param string $hash
     * @return boolean
     */
    public function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Checks if hash is out of date with the current algorithm
     * @param string $hash
     * @return boolean
     */
    public function needsRehash($hash)
    {
        return password_needs_rehash($hash, $this->algorithm, $this->options);
    }

    /**
     * Set new algorithm
     * @param int $algorithm
     * @return NativeHasher
     */
    public function setAlgorithm($algorithm)
    {
        $this->algorithm = $algorithm;
        return $this;
    }

    /**
     * Set new options for algorithm
     * @param array $options
     * @return NativeHasher
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }
}
