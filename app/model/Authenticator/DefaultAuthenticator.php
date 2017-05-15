<?
namespace App\Model\Authenticator;

use App\Model\Entity\UserLogModel;
use App\Model\Entity\UserModel;
use App\Model\Entity\UserPriceForProductModel;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;

class DefaultAuthenticator extends \Nette\Object implements IAuthenticator
{

    /**
     * @var UserModel
     */
    private $userModel;

    function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * Performs an authentication
     *
     * @param  array
     *
     * @return void
     * @throws AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        $username = $credentials[self::USERNAME];
        $password = $credentials[self::PASSWORD];

        $sql = $this->userModel->getFluent()->where('login = %s', $username, 'AND activate = 1');

        $row = $sql->fetch();
//        var_dump($this->userModel->getHash($password));
//        exit;
        /* ak to bolo stratene heslo */
        if (count($sql) == 1 AND $row->new_password == $this->userModel->getHash($password)) {
            $this->userModel->update(array('password' => $this->userModel->getHash($password), 'new_password' => null), $row->id_user);
            $row->password = $row->new_password;
        }

        if (count($sql) == 0 OR $row->password !== $this->userModel->getHash($password)) {
            throw new AuthenticationException('Nespráne heslo alebo meno.', self::INVALID_CREDENTIAL);
        }

        $roles = $this->userModel->getRoles($row->id_user);

        unset($row->password);

        return new Identity($row->id_user, $roles, $row);
    }

}