<?php

namespace Laasti\Warden\Sessions;

class NativeSession implements SessionInterface
{

    protected $key;

    public function __construct($key)
    {
        $this->key = $key;
        $this->startSession();
    }

    protected function startSession()
    {
        //Make sure the session has not already started
        if (session_id() == '' && !headers_sent()) {
            session_start();
        }
    }

    public function set($value)
    {
        $_SESSION[$this->key] = $value;
    }

    public function get()
    {
        return isset($_SESSION[$this->key]) ? $_SESSION[$this->key] : null;
    }

    public function remove()
    {
        if (isset($_SESSION[$this->key])) {
            unset($_SESSION[$this->key]);
        }
    }

    /**
     * Called upon destruction of the native session handler.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->writeSession();
    }

    protected function writeSession()
    {
        session_write_close();
    }
}
