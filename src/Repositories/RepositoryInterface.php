<?php

namespace Laasti\Warden\Repositories;

interface RepositoryInterface
{
    /**
     * Get a user by its identifier used to log in
     * @param string|int $identifier
     */
    public function getByIdentifier($identifier);

    /**
     * Get a user by its id, used to log in user from session
     * @param string|int $id
     */
    public function getById($id);
}
