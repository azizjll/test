<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $userPasswordEncoderInterface;
    public function __construct(UserPasswordEncoderInterface $userPasswordEncoderInterface)
    {
        $this->userPasswordEncoderInterface=$userPasswordEncoderInterface;
    }
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $password_hashed=$this->userPasswordEncoderInterface->encodePassword($user,"aziz1234");
        $user->setUsername('azizchahlaoui');
        $user->setEmail('azizchahlaoui7@gmail.com');
        $user->setPassword($password_hashed);
        $manager->persist($user);
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
