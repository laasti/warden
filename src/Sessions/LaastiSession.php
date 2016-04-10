<?php

namespace Laasti\Warden\Sessions;

class LaastiSession implements SessionInterface
{

    protected $session;
    protected $key;

    public function __construct($key, \Laasti\Sessions\Session $session = null)
    {
        $this->session = $session;
        $this->key = $key;
    }

    public function set($value)
    {
        $this->session->set($this->key, $value);
    }

    public function get()
    {
        return $this->session->get($this->key);
    }

    public function remove()
    {
        $this->session->remove($this->key);
    }
    
}
