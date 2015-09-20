# Laasti/warden

A PHP framework agnostic authentication and authorization package.
It does not and will never provide any way to create users. By default, it simply reads the users table in the provided database.
You are free to use whatever method fits you best (PDO, ORMs...).

It is a pretty simple library. You won't find any advanced security features like in Sentinel, at least for now.
The password are hashed using the latest password_ functions from PHP 5.5.

Keep in mind though that you should ensure your sessions are well protected against known vulnerabilities.
You should also add an activation and reset mechanism. This package might provide them in the future.
A throttling mechanism against brute force attacks can also increase the security.

## Installation

```
composer require laasti/warden
```

The native hasher makes use of PHP 5.5 password_* functions.

For PHP 5.4, you need another library:

```
composer require ircmaxell/password-compat
```

## Usage

Roles should be UPPERCASED and permissions, lowercased.

Uses PHP's native sessions, just be sure to register a session handler using SessionHandlerInterface and session_set_save_handler()
or you can implement your own SessionInterface

Uses PHP 5.5's native password functions by default, for backward compatibility you will require ircmaxell/password-compat
or you can provide your own HasherInterface

Provides a basic PDO repository to retrieve users from database, but you can create your own RepositoryInterface

```php

$pdo = new PDO($dsn, $user, $password);
//By default the repository looks for a table "users" with columns: id, email, password, roles, permissions
//Roles and permissions are comma-delimited.
$repo = new Laasti\Warden\Repositories\PdoUserRepository($pdo);
$warden = new Laasti\Warden\Warden();

//API
$warden->admit($identifier, $password); //Logs in user matching credentials
$warden->isAdmitted(); //User is logged in
$warden->couldBeAdmitted($identifier, $password); //Checks if user could be logged in
$warden->admitUser($user); //Logs in provided user, useful to bypass authentication
$warden->currentUser(); //Logged in user, instance of GuestUser if none
$warden->dismiss(); //Logs out current user
$warden->grantAccess($roleOrPermission); //Check for role or permission in current user
$warden->grantAccessByPermission($permission); // Grant access if user matches permission
$warden->grantAccessByPermissions($permissions); // Grant access if user matches all permissions
$warden->grantAccessByRole($role); // Grant access if user matches role
$warden->grantAccessByRoles($roles); // Grant access if user matches all roles
$warden->getHasher()->hash($password); //Get a hash for a password

//Using Roles Dictionary
//Roles can inherit permissions by default, to assign permissions to roles
//you need to define a roles dictionary using an array
$dictionary = [
    'ROLE' => ['permission', 'permission2']
];
$warden->setRolesDictionary($dictionary);
```

## Contributing

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request :D

## History

See CHANGELOG.md for more information.

## Credits

Author: Sonia Marquette (@nebulousGirl)

## License

Released under the MIT License. See LICENSE.txt file.