<?php

namespace App\Repository;

use App\Entity\Admin;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private $manager,$passwordEncoder;

    public function __construct(ManagerRegistry $registry,EntityManagerInterface $manager,UserPasswordEncoderInterface $passwordEncoder)
    {
        parent::__construct($registry, User::class);
        $this->passwordEncoder=$passwordEncoder;
        $this->manager = $manager;
    }

    public function retriveSubAdmins($id = null)
    {
        $qb = $this->manager->createQueryBuilder();
        $qb->select('user')
            ->from('App:User', 'user')
            ->where("user.roles LIKE :role_admin")
            ->setParameter( 'role_admin' , '%ROLE_ADMIN%');

        if ($id !== null) {
            $qb->andWhere("user.id = :id")
                ->setParameter('id',$id);
        }
        return  $qb->getQuery()->getResult();
  }
    public function retriveSuperAdmins()
    {
        $qb = $this->manager->createQueryBuilder();
        $qb->select('user.email')
            ->from('App:User', 'user')
            ->where("user.roles LIKE :role_admin")
            ->setParameter( 'role_admin' , '%ROLE_SUPER_ADMIN%');
        return  $qb->getQuery()->getResult()
            ;
    }
    public function saveAdmin($newUser,$data)
    {
      $encoded = $this->passwordEncoder->encodePassword($newUser, $data['password']);
        $newUser
            ->setEmail($data['email'])
            ->setPassword($encoded)
            ->setName($data['name'])
            ->setSuperAdmin($data['superAdmin'])
            ->setRoles($data['roles']);

        $this->manager->persist($newUser);
        $this->manager->flush();
    }


    public function removeAdmin(User $user)
    {
        $this->manager->remove($user);
        $this->manager->flush();
    }
    public function updateUser(User $user): User
    {
        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }
    public function userPasswordMatch($userObject,$currentPassword):bool
    {
       return $this->passwordEncoder->isPasswordValid($userObject, $currentPassword);
     }
    public function passwordUpdate($id,$newPassword)
    {

        $user=$this->find($id);
        $encoded = $this->passwordEncoder->encodePassword($user, $newPassword);
        $user->setPassword($encoded);
        $this->manager->persist($user);
        $this->manager->flush();
    }


    public function validateUserId($id) {
        return  $this->find($id);
    }
}
