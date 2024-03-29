<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class AdminFixtures extends Fixture
{

    private $passwordEncoder;
    private $tokenGenerator;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder,TokenGeneratorInterface $tokenGenerator)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenGenerator = $tokenGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $token = $this->tokenGenerator->generateToken();
        $user->setResetToken($token);
        $password_hashed = $this->passwordEncoder->encodePassword($user,"admin");
        $user->setUsername("Admin");
        $user->setEtat(true);
        $user->setRoles(['ROLE_ADMIN']);
        $dateNaissance = new \DateTime('2001-06-20');
        $user->setDateNaissance($dateNaissance);
        $user->setEmail("admin@gmail.com");
        $user->setNumero("95124032");
        
        $user->setPassword($password_hashed);
        $manager->persist($user);

        $manager->flush();
    }
}
