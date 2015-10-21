<?php

namespace Laasti\Warden;

use Laasti\Warden\Hashers\HasherInterface;
use Laasti\Warden\Hashers\NativeHasher;
use Laasti\Warden\Repositories\RepositoryInterface;
use Laasti\Warden\Sessions\NativeSession;
use Laasti\Warden\Sessions\SessionInterface;
use Laasti\Warden\Users\GuestUser;
use Laasti\Warden\Users\UserInterface;

class Warden
{

    /**
     * Password hasher
     * @var HasherInterface
     */
    protected $hasher;

    /**
     * Repository to find users
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * Session to store logged in user id
     * @var SessionInterface
     */
    protected $session;

    /**
     * Logged in user or GuestUser when no logged in user
     * @var UserInterface
     */
    protected $currentUser;

    /**
     * An array of roles to permissions association
     * ['ROLE' => ['mypermission]]
     *
     * @var array
     */
    protected $roleDictionary;

    /**
     *
     * @param RepositoryInterface $repository
     * @param SessionInterface $session
     * @param HasherInterface $hasher
     */
    public function __construct(RepositoryInterface $repository, SessionInterface $session = null, HasherInterface $hasher = null)
    {
        $this->repository = $repository;
        $this->session = $session ? : new NativeSession('_warden');
        $this->hasher = $hasher ? : new NativeHasher();

    }

    /**
     * Attempts to log in user from supplied credentials
     *
     * @param string $identifier
     * @param string $password
     * @return boolean
     */
    public function admit($identifier, $password)
    {
        $user = $this->repository->getByIdentifier($identifier);
        
        if ($user instanceof UserInterface && $this->hasher->verify($password, $user->getPasswordHash())) {
            $this->admitUser($user);
            return true;
        } else if (is_object($user) && !$user instanceof UserInterface) {
            throw new \RuntimeException('Your users must implement "Laasti\Warden\Users\UserInterface".');
        }

        return false;
    }

    /**
     * Log in the provided user, no questions asked
     * @param UserInterface $user
     * @return boolean
     */
    public function admitUser(UserInterface $user)
    {
        $this->currentUser = $user;
        $this->session->set($user->getId());
        return true;
    }

    /*
     * Get logged in user
     */
    public function currentUser()
    {
        if (is_null($this->currentUser)) {
            $this->setUserFromSession();
        }
        return $this->currentUser;
    }

    /**
     * Checks if a user could be logged in with those credentials
     * @param string $identifier
     * @param string $password
     * @return boolean
     */
    public function couldBeAdmitted($identifier, $password)
    {
        $user = $this->repository->getByIdentifier($identifier);
        
        if ($user instanceof UserInterface && $this->hasher->verify($password, $user->getPasswordHash())) {
            return true;
        } else if (is_object($user) && !($user instanceof UserInterface)) {
            throw new \RuntimeException('Your users must implement "Laasti\Warden\Users\UserInterface".');
        }

        return false;
    }

    /**
     * Checks if a user is logged in
     * @param UserInterface $user
     * @return bool
     */
    public function isAdmitted(UserInterface $user = null)
    {
        if (is_null($user)) {
            return !$this->currentUser() instanceof GuestUser;
        }

        return $user->getIdentifier() === $this->currentUser()->getIdentifier();
    }

    /**
     * Logs current user out
     */
    public function dismiss()
    {
        $this->currentUser = new GuestUser();
        $this->session->remove();
    }

    /**
     * Grants access by roles or permissions. Be careful to use different names for roles and permissions
     * You could UPPERCASE your roles and lowercase your permissions.
     *
     * @param string|array $roleOrPermission
     * @param UserInterface $user
     */
    public function grantAccess($roleOrPermission, UserInterface $user = null)
    {
        $user = $user ? : $this->currentUser();

        if (is_array($roleOrPermission) && $this->grantAccessByRoles($roleOrPermission, $user)) {
            return true;
        } else if (is_array($roleOrPermission) && $this->grantAccessByPermissions($roleOrPermission, $user)) {
            return true;
        } else if ($this->grantAccessByRole($roleOrPermission, $user)) {
            return true;
        } else if ($this->grantAccessByPermission($roleOrPermission, $user)) {
            return true;
        }

        return false;
    }

    /**
     * Check if user has the permission
     * @param string $permission
     * @param UserInterface $user
     * @return boolean
     */
    public function grantAccessByPermission($permission, UserInterface $user = null)
    {
        $user = $user ? : $this->currentUser();
        return in_array($permission, $this->gatherPermissions($user));
    }

    /**
     * Check if user has all permissions
     * @param string $permissions
     * @param UserInterface $user
     * @return boolean
     */
    public function grantAccessByPermissions($permissions, UserInterface $user = null)
    {
        $user = $user ? : $this->currentUser;
        return count(array_diff($permissions, $this->gatherPermissions($user))) === 0;
    }

    /**
     * Check if user has all roles
     * @param array $roles
     * @param UserInterface $user
     * @return boolean
     */
    public function grantAccessByRoles($roles, UserInterface $user = null)
    {
        $user = $user ? : $this->currentUser();
        return count(array_diff($roles, $user->getRoles())) === 0;
    }

    /**
     * Check if user has the role
     * @param string $role
     * @param UserInterface $user
     * @return boolean
     */
    public function grantAccessByRole($role, UserInterface $user = null)
    {
        $user = $user ? : $this->currentUser();
        return in_array($role, $user->getRoles());
    }

    /**
     * Get current hasher
     * @return HasherInterface
     */
    public function getHasher()
    {
        return $this->hasher;
    }

    /**
     * Get current user repository
     * @return RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Get current session handler
     * @return SessionInterface
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Set new hasher
     * @param HasherInterface $hasher
     * @return Warden
     */
    public function setHasher(HasherInterface $hasher)
    {
        $this->hasher = $hasher;
        return $this;
    }

    /**
     * Set new repository
     * @param RepositoryInterface $repository
     * @return Warden
     */
    public function setRepository(RepositoryInterface $repository)
    {
        $this->repository = $repository;
        return $this;
    }

    /**
     * Set new session
     * @param SessionInterface $session
     * @return Warden
     */
    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
        return $this;
    }

    /**
     * Sets the permissions for each role
     * @param array $roleDictionary
     */
    public function setRoleDictionary($roleDictionary)
    {
        $this->roleDictionary = $roleDictionary;

        return $this;
    }

    /**
     * Retrieve logged in user
     */
    protected function setUserFromSession()
    {
        $id = $this->session->get();

        if ($id) {
            $user = $this->repository->getById($this->session->get());
            if ($user instanceof UserInterface) {
                $this->admitUser($user);
                return;
            }
        }

        $this->currentUser = new GuestUser();
    }

    /**
     * Gather permissions from user roles
     * @param UserInterface $user
     * @return array
     */
    protected function gatherPermissions(UserInterface $user = null)
    {
        $user = $user ?: $this->currentUser();
        $roles = $user->getRoles();
        $permissions = $user->getPermissions();

        foreach ($roles as $role) {
            if (isset($this->roleDictionary[$role])) {
                $permissions = array_merge($permissions, $this->roleDictionary[$role]);
            }
        }

        return $permissions;
    }

}
