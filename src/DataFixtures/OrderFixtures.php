<?php

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\User;
use App\Enum\OrderStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class OrderFixtures extends Fixture implements DependentFixtureInterface
{
    private const USER_COUNT = 25;
    private const PRODUCT_COUNT = 16;

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $orderIndex = 0;

        for ($i = 0; $i < 10; $i++) {
            $this->createOrder(
                $manager,
                OrderStatus::EN_PREPARATION,
                $this->faker->dateTimeBetween('-7 days', 'now'),
                $orderIndex++
            );
        }

        for ($i = 0; $i < 15; $i++) {
            $this->createOrder(
                $manager,
                OrderStatus::EXPEDIEE,
                $this->faker->dateTimeBetween('-14 days', '-3 days'),
                $orderIndex++
            );
        }

        for ($i = 0; $i < 20; $i++) {
            $this->createOrder(
                $manager,
                OrderStatus::LIVREE,
                $this->faker->dateTimeBetween('-6 months', '-14 days'),
                $orderIndex++
            );
        }

        for ($i = 0; $i < 5; $i++) {
            $this->createOrder(
                $manager,
                OrderStatus::ANNULEE,
                $this->faker->dateTimeBetween('-3 months', '-1 week'),
                $orderIndex++
            );
        }

        $manager->flush();
    }

    private function createOrder(
        ObjectManager $manager,
        OrderStatus $status,
        \DateTimeInterface $createdAt,
        int $orderIndex
    ): void {
        $order = new Order();

        $userIndex = $this->faker->numberBetween(1, self::USER_COUNT);
        $user = $this->getReference('user_' . $userIndex, User::class);
        $order->setUser($user);

        $order->setStatus($status);
        $order->setCreatedAt($createdAt);

        $itemCount = $this->faker->numberBetween(1, 5);
        $usedProducts = [];

        for ($i = 0; $i < $itemCount; $i++) {
            do {
                $productIndex = $this->faker->numberBetween(0, self::PRODUCT_COUNT - 1);
            } while (in_array($productIndex, $usedProducts));

            $usedProducts[] = $productIndex;

            $product = $this->getReference('product_' . $productIndex, Product::class);

            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($this->faker->numberBetween(1, 3));
            $orderItem->setProductPrice($product->getPrice());

            $order->addOrderItem($orderItem);
        }

        $manager->persist($order);
        $this->addReference('order_' . $orderIndex, $order);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ProductFixtures::class,
        ];
    }
}
