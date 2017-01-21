<?php

namespace Laasti\Warden\Tests;

use Laasti\Warden\Repositories\PdoUserRepository;
use Laasti\Warden\Users\GuestUser;
use Laasti\Warden\Users\PdoUser;
use Laasti\Warden\Warden;
use PDO;

/**
 * WardenTest Class
 *
 */
class WardenTest extends \PHPUnit_Framework_TestCase
{

    public function testDefaultBehavior()
    {
        $email = 'info@pixelcircus.ca';
        $password = '123456';
        $wrongPassword = '123456789';

        $pdo = new PDO('mysql:host=localhost;dbname=pixms2', 'root', '');
        $pdoRepo = new PdoUserRepository($pdo, 'administrators');
        $warden = new Warden($pdoRepo);
        $warden->setRoleDictionary([
            'SUPERADMIN' => ['role.permission']
        ]);
        $fakeUser = new PdoUser([
            'email' => $email,
            'permissions' => ['some.permission', 'another.permission'],
            'roles' => ['SUPERADMIN'],
        ]);

        $this->assertTrue($warden->currentUser() instanceof GuestUser);
        $this->assertTrue(!$warden->isAdmitted());
        $this->assertTrue(!$warden->couldBeAdmitted($email, $wrongPassword));
        $this->assertTrue(!$warden->admit($email, $wrongPassword));

        if ($warden->couldBeAdmitted($email, $password) && $warden->admit($email, $password)) {
            $user = $warden->currentUser();
            $this->assertTrue($warden->isAdmitted());
            $this->assertTrue($user instanceof PdoUser);
            $this->assertTrue($warden->isAdmitted($user));

            $warden->dismiss();
            $this->assertTrue(!$warden->isAdmitted());
            $this->assertTrue($warden->currentUser() instanceof GuestUser);

            $warden->admitUser($fakeUser);
            $this->assertTrue($warden->grantAccessByPermission('some.permission'));
            $this->assertTrue(!$warden->grantAccessByPermission('not.permission'));
            $this->assertTrue(!$warden->grantAccessByPermissions(['some.permission', 'not.permission']));
            $this->assertTrue($warden->grantAccessByPermissions(['some.permission', 'another.permission']));
            $this->assertTrue(!$warden->grantAccessByRole('GOD'));
            $this->assertTrue($warden->grantAccessByRole('SUPERADMIN'));
            $this->assertTrue(!$warden->grantAccessByRoles(['SUPERADMIN', 'GOD']));
            $this->assertTrue($warden->grantAccessByRoles(['SUPERADMIN']));
            $this->assertTrue($warden->grantAccess(['SUPERADMIN']));
            $this->assertTrue(!$warden->grantAccess(['GOD']));
            $this->assertTrue($warden->grantAccess('SUPERADMIN'));
            $this->assertTrue($warden->grantAccess('some.permission'));
            $this->assertTrue($warden->grantAccess(['some.permission', 'another.permission']));
            $this->assertTrue($warden->grantAccess('role.permission'));
        } else {
            $this->fail();
        }
    }

    public function testInvalidUserType()
    {
        $fakeRepo = $this->getMock('Laasti\Warden\Repositories\RepositoryInterface');
        $fakeRepo->method('getByIdentifier')->will($this->returnValue(new \stdClass()));
        $warden = new Warden($fakeRepo);

        $this->setExpectedException('RuntimeException');

        $warden->admit('fake@example.com', '123456');
    }
}
