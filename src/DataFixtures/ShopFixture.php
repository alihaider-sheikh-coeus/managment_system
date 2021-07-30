<?php


namespace App\DataFixtures;


use App\Entity\Shop;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ShopFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 10; $i++) {
            $shop = new Shop();
            $shop->setName('product ' . $i);
            $shop->setStatus("open");
            $manager->persist($shop);
        }
        $manager->flush();
    }
}