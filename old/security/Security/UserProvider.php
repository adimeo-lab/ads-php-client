<?php

namespace AdimeoDataSuite\Bundle\ADSSecurityBundle\Security;

use AdimeoDataSuite\Index\IndexManager;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
  /** @var  IndexManager */
  private $indexManager;

  public function __construct(IndexManager $indexManager){
    $this->indexManager = $indexManager;
  }

  public function loadUserByUsername($username)
  {
    $user = $this->getUser($username);
    if($user == null){
      throw new UsernameNotFoundException();
    }
    return $user;
  }

  public function refreshUser(UserInterface $user)
  {
    return $this->getUser($user->getUsername());
  }

  public function supportsClass($class)
  {
    return $class == User::class;
  }

  /**
   * @param string $username
   * @return UserInterface
   */
  private function getUser($username) {
    try {
      return $this->indexManager->findObject('user', $username);
    }
    catch(\Exception $ex) {
      return null;
    }
  }

}