<?php


namespace App\DataFixtures;


use App\Entity\Shop;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixture extends Fixture
{
    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder=$passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {

            $user1 = new User();
            $user1->setName('superUser');
        $user1->setPassword($this->passwordEncoder->encodePassword(
            $user1,
            '12345678'
        ));
            $user1->setEmail("superAdmin@gmail.com");
            $user1->setSuperAdmin(true);
            $user1->setRoles(["ROLE_SUPER_ADMIN"]);
            $manager->persist($user1);

        $user2 = new User();
        $user2->setName('simpleUser');
        $user2->setPassword($this->passwordEncoder->encodePassword(
            $user2,
            '12345678'
        ));
        $user2->setEmail("simpleUser@gmail.com");
        $user2->setSuperAdmin(false);
        $user2->setRoles(["ROLE_USER"]);
        $manager->persist($user2);

        $manager->flush();
    }
}