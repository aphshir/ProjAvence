<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\CreditCard;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private Generator $faker;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $this->createAdministrator($manager);

        for ($i = 1; $i <= 25; $i++) {
            $this->createTestUser($manager, $i);
        }

        $manager->flush();
    }

    private function createAdministrator(ObjectManager $manager): void
    {
        $admin = new User();

        $adminEmail = $_ENV['ADMINISTRATOR_EMAIL'] ?? 'admin@nightmarket.com';
        $adminPassword = $_ENV['ADMINISTRATOR_PASSWORD'] ?? 'admin123';

        $admin->setEmail($adminEmail);
        $admin->setFirstName('Admin');
        $admin->setLastName('NightMarket');
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        $hashedPassword = $this->passwordHasher->hashPassword($admin, $adminPassword);
        $admin->setPassword($hashedPassword);

        $this->addAddresses($admin, 2);

        $this->addCreditCard($admin);

        $manager->persist($admin);
        $this->addReference('user_admin', $admin);
    }

    private function createTestUser(ObjectManager $manager, int $index): void
    {
        $user = new User();

        $user->setEmail("user{$index}@test.com");
        $user->setFirstName($this->faker->firstName());
        $user->setLastName($this->faker->lastName());
        $user->setRoles(['ROLE_USER']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, 'password');
        $user->setPassword($hashedPassword);

        $addressCount = $this->faker->numberBetween(1, 2);
        $this->addAddresses($user, $addressCount);

        $this->addCreditCard($user);

        $manager->persist($user);
        $this->addReference('user_' . $index, $user);
    }

    private function addAddresses(User $user, int $count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $address = new Address();
            $address->setStreet($this->faker->streetAddress());
            $address->setPostalCode($this->faker->postcode());
            $address->setCity($this->faker->city());
            $address->setCountry('France');
            $address->setUser($user);

            $user->addAddress($address);
        }
    }

    private function addCreditCard(User $user): void
    {
        $creditCard = new CreditCard();

        $cardNumber = $this->faker->creditCardNumber('Visa');
        $creditCard->setNumber($cardNumber);

        $expirationDate = $this->faker->creditCardExpirationDateString();
        $creditCard->setExpirationDate($expirationDate);

        $cvv = $this->faker->numberBetween(100, 999);
        $creditCard->setCvv((string) $cvv);

        $creditCard->setUser($user);
        $user->addCreditCard($creditCard);
    }
}
