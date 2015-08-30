<?php

namespace Laasti\Warden\Sessions;

interface SessionInterface
{
    public function __construct($key);
    public function get();
    public function set($value);
    public function remove();
}
