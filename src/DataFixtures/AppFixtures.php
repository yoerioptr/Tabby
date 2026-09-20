<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
        //
    }

    public function load(ObjectManager $manager): void
    {
        $adminUser = new User();
        $adminUser->setEmail('email@example.com');
        $adminUser->setPassword($this->hasher->hashPassword($adminUser, 'strong-password'));
        $adminUser->setFirstName('Benny');
        $adminUser->setLastName('Lava');
        $adminUser->setDescription('Tabby administrator');
        $adminUser->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

        $manager->persist($adminUser);

        $manager->flush();
    }
}
