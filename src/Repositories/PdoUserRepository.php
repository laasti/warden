<?php

namespace Laasti\Warden\Repositories;

use Laasti\Warden\Users\PdoUser;
use PDO;
use PDOStatement;

class PdoUserRepository implements RepositoryInterface
{

    /**
     *
     * @var PDO
     */
    protected $pdo;
    protected $table;
    protected $identifier;
    protected $id;
    protected $permissions;
    protected $roles;

    /**
     *
     * @var PDOStatement
     */
    protected $byIdStatement;

    /**
     *
     * @var PDOStatement
     */
    protected $byIdentifierStatement;

    public function __construct(
        PDO $pdo,
        $table = 'users',
        $identifier = 'email',
        $id = 'id',
        $permissions = 'permissions',
        $roles = 'roles'
    ) {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->identifier = $identifier;
        $this->id = $id;
        $this->permissions = $permissions;
        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        if (is_null($this->byIdStatement)) {
            $this->byIdStatement = $this->pdo->prepare('SELECT * FROM ' . $this->table . '  WHERE ' . $this->id . ' = :id LIMIT 1');
        }
        $result = $this->byIdStatement->execute([':id' => $id]);

        if ($result) {
            $userInfo = $this->byIdStatement->fetch(PDO::FETCH_ASSOC);
            return new PdoUser($userInfo);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getByIdentifier($identifier)
    {
        if (is_null($this->byIdentifierStatement)) {
            $this->byIdentifierStatement = $this->pdo->prepare('SELECT * FROM ' . $this->table . '  WHERE ' . $this->identifier . ' = :identifier');
        }
        $result = $this->byIdentifierStatement->execute([':identifier' => $identifier]);

        if ($result) {
            $userInfo = $this->byIdentifierStatement->fetch(PDO::FETCH_ASSOC);
            $userInfo[$this->permissions] = explode(',', $userInfo[$this->permissions]);
            $userInfo[$this->roles] = explode(',', $userInfo[$this->roles]);
            return new PdoUser($userInfo);
        }

        return null;
    }
}
