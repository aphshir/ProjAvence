<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\Product;
use App\Enum\ProductStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    private int $productIndex = 0;

    public function load(ObjectManager $manager): void
    {
        $this->createProduct(
            $manager,
            'Vandal RGX 11z Pro',
            'Le Vandal RGX 11z Pro est une arme futuriste avec des effets visuels et sonores électroniques uniques. Effets de kill améliorés et animations fluides.',
            '29.99',
            50,
            ProductStatus::DISPONIBLE,
            'category_rifles',
            [
                ['https://media.valorant-api.com/weaponskins/e5490f71-455b-74ad-f762-f5a876d4dff9/displayicon.png', 'Vandal RGX 11z Pro']
            ]
        );

        $this->createProduct(
            $manager,
            'Phantom Reaver',
            'Le Phantom Reaver apporte une touche gothique à votre arsenal. Design sombre avec des effets violets envoûtants et un finisher spectaculaire.',
            '24.99',
            75,
            ProductStatus::DISPONIBLE,
            'category_rifles',
            [
                ['https://media.valorant-api.com/weaponskins/044b28ba-4c3b-d315-140d-d9a249da5567/displayicon.png', 'Phantom Reaver']
            ]
        );

        $this->createProduct(
            $manager,
            'Vandal Prime',
            'Le Vandal Prime combine élégance et puissance avec ses finitions dorées et ses effets lumineux premium. Un classique indémodable.',
            '34.99',
            30,
            ProductStatus::DISPONIBLE,
            'category_rifles',
            [
                ['https://media.valorant-api.com/weaponskins/b9ee2457-481c-6776-3f5b-0ca8e8f90c89/displayicon.png', 'Vandal Prime']
            ]
        );

        $this->createProduct(
            $manager,
            'Vandal Elderflame',
            'Le légendaire Vandal Elderflame transforme votre arme en dragon vivant. Effets pyrotechniques spectaculaires.',
            '44.99',
            0,
            ProductStatus::EN_RUPTURE,
            'category_rifles',
            [
                ['https://media.valorant-api.com/weaponskins/18609205-4edb-5966-cff8-0fba0230ba1e/displayicon.png', 'Vandal Elderflame']
            ]
        );

        $this->createProduct(
            $manager,
            'Vandal Glitchpop',
            'Le Vandal Glitchpop offre un style cyberpunk unique avec des couleurs néon vibrantes et des effets glitch.',
            '32.99',
            40,
            ProductStatus::DISPONIBLE,
            'category_rifles',
            [
                ['https://media.valorant-api.com/weaponskins/74789f33-4632-8052-96d7-258538721a32/displayicon.png', 'Vandal Glitchpop']
            ]
        );

        $this->createProduct(
            $manager,
            'Phantom Glitchpop',
            'Le Phantom Glitchpop arbore un design cyberpunk avec des effets visuels néon et des animations uniques.',
            '32.99',
            35,
            ProductStatus::DISPONIBLE,
            'category_rifles',
            [
                ['https://media.valorant-api.com/weaponskins/25a7f0f2-4bce-7e45-b4b0-ca9264f5dfcc/displayicon.png', 'Phantom Glitchpop']
            ]
        );

        $this->createProduct(
            $manager,
            'Phantom Ion',
            'Le Phantom Ion offre des effets énergétiques bleus futuristes avec des animations de kill impressionnantes.',
            '27.99',
            55,
            ProductStatus::DISPONIBLE,
            'category_rifles',
            [
                ['https://media.valorant-api.com/weaponskins/e86bf7e4-4dd3-fbee-533b-fa875344bbaf/displayicon.png', 'Phantom Ion']
            ]
        );

        $this->createProduct(
            $manager,
            'Phantom Singularity',
            'Le Phantom Singularity propose des effets cosmiques uniques avec des particules violettes hypnotiques.',
            '29.99',
            45,
            ProductStatus::DISPONIBLE,
            'category_rifles',
            [
                ['https://media.valorant-api.com/weaponskins/5eec4ce6-443d-e9b5-4c5b-2b967d426bd3/displayicon.png', 'Phantom Singularity']
            ]
        );

        $this->createProduct(
            $manager,
            'Vandal Champions 2023',
            'Le Vandal Champions 2023 célèbre les champions du monde avec un design premium doré et noir.',
            '49.99',
            20,
            ProductStatus::DISPONIBLE,
            'category_rifles',
            [
                ['https://media.valorant-api.com/weaponskins/b0f65660-4c51-13b7-9d01-e29a1e2879b0/displayicon.png', 'Vandal Champions 2023']
            ]
        );

        $this->createProduct(
            $manager,
            'Sheriff Reaver',
            'Le Sheriff Reaver apporte une esthétique dark et mystérieuse à votre arme de poing préférée.',
            '19.99',
            60,
            ProductStatus::DISPONIBLE,
            'category_pistols',
            [
                ['https://media.valorant-api.com/weaponskins/a40a6ce2-462c-c864-5d30-7b9408b98d3d/displayicon.png', 'Sheriff Reaver']
            ]
        );

        $this->createProduct(
            $manager,
            'Sheriff Ion',
            'Le Sheriff Ion offre un design futuriste minimaliste avec des effets énergétiques bleus.',
            '14.99',
            80,
            ProductStatus::DISPONIBLE,
            'category_pistols',
            [
                ['https://media.valorant-api.com/weaponskins/83778c03-45a3-67a2-3c89-6b8598327d58/displayicon.png', 'Sheriff Ion']
            ]
        );

        $this->createProduct(
            $manager,
            'Classic Prime',
            'Le Classic Prime offre des finitions dorées élégantes pour votre pistolet de départ.',
            '12.99',
            100,
            ProductStatus::DISPONIBLE,
            'category_pistols',
            [
                ['https://media.valorant-api.com/weaponskins/d653f4a7-4e92-2559-0a97-2c9d46d009b3/displayicon.png', 'Classic Prime']
            ]
        );

        $this->createProduct(
            $manager,
            'Frenzy Glitchpop',
            'Le Frenzy Glitchpop apporte le style cyberpunk à votre pistolet automatique.',
            '11.99',
            90,
            ProductStatus::DISPONIBLE,
            'category_pistols',
            [
                ['https://media.valorant-api.com/weaponskins/5596d764-4b62-210b-59db-7982e9d4c23f/displayicon.png', 'Frenzy Glitchpop']
            ]
        );

        $this->createProduct(
            $manager,
            'Operator Ion',
            'L\'Operator Ion offre un design futuriste avec des effets énergétiques bleus pour les snipers.',
            '39.99',
            25,
            ProductStatus::DISPONIBLE,
            'category_snipers',
            [
                ['https://media.valorant-api.com/weaponskins/bbf8ffb9-49c0-75c0-cc7d-8f8f03a4bd36/displayicon.png', 'Operator Ion']
            ]
        );

        $this->createProduct(
            $manager,
            'Operator Reaver',
            'L\'Operator Reaver apporte une esthétique gothique dark à votre sniper préféré.',
            '42.99',
            20,
            ProductStatus::DISPONIBLE,
            'category_snipers',
            [
                ['https://media.valorant-api.com/weaponskins/aecab890-43b7-d719-06bc-9295e3d116dc/displayicon.png', 'Operator Reaver']
            ]
        );

        $this->createProduct(
            $manager,
            'Operator Elderflame',
            'L\'Operator Elderflame transforme votre sniper en arme draconique avec des effets de feu.',
            '47.99',
            15,
            ProductStatus::DISPONIBLE,
            'category_snipers',
            [
                ['https://media.valorant-api.com/weaponskins/d722313d-43cb-b38d-7841-75880a3ed2cb/displayicon.png', 'Operator Elderflame']
            ]
        );

        $this->createProduct(
            $manager,
            'Spectre RGX 11z Pro',
            'Le Spectre RGX 11z Pro offre des effets électroniques futuristes pour votre SMG.',
            '22.99',
            50,
            ProductStatus::DISPONIBLE,
            'category_smgs',
            [
                ['https://media.valorant-api.com/weaponskins/4f0c9544-469c-0c62-df2e-95b15d6f2333/displayicon.png', 'Spectre RGX 11z Pro']
            ]
        );

        $this->createProduct(
            $manager,
            'Spectre Singularity',
            'Le Spectre Singularity propose des effets cosmiques violets pour votre SMG.',
            '21.99',
            45,
            ProductStatus::DISPONIBLE,
            'category_smgs',
            [
                ['https://media.valorant-api.com/weaponskins/0eab3e5c-4de4-e221-34fb-2ab435c89eb6/displayicon.png', 'Spectre Singularity']
            ]
        );

        $this->createProduct(
            $manager,
            'RGX 11z Pro Blade',
            'Le légendaire couteau RGX avec des animations fluides et des effets électroniques. Le must-have de tout collectionneur.',
            '49.99',
            15,
            ProductStatus::DISPONIBLE,
            'category_melee',
            [
                ['https://media.valorant-api.com/weaponskins/9fb366b6-46df-a722-0cf2-9c9b85936f17/displayicon.png', 'RGX 11z Pro Blade']
            ]
        );

        $this->createProduct(
            $manager,
            'Champions 2023 Kunai',
            'Le kunai exclusif Champions 2023. Design premium avec effets dorés et particules scintillantes.',
            '59.99',
            10,
            ProductStatus::DISPONIBLE,
            'category_melee',
            [
                ['https://media.valorant-api.com/weaponskins/27f27500-491c-32d4-1db6-1f85e479c103/displayicon.png', 'Champions 2023 Kunai']
            ]
        );

        $this->createProduct(
            $manager,
            'Reaver Knife',
            'Le couteau Reaver apporte une esthétique gothique dark à votre arme de mêlée.',
            '44.99',
            20,
            ProductStatus::DISPONIBLE,
            'category_melee',
            [
                ['https://media.valorant-api.com/weaponskins/0aecb2b8-49cc-560e-42c7-6cbce44f05cf/displayicon.png', 'Reaver Knife']
            ]
        );

        $this->createProduct(
            $manager,
            'Prime Axe',
            'La hache Prime offre des finitions dorées premium pour votre arme de mêlée.',
            '39.99',
            25,
            ProductStatus::DISPONIBLE,
            'category_melee',
            [
                ['https://media.valorant-api.com/weaponskins/e100dff1-4cf5-54ec-aa65-6fadbc22973b/displayicon.png', 'Prime Axe']
            ]
        );

        $this->createProduct(
            $manager,
            'Ion Energy Sword',
            'L\'épée énergétique Ion avec des effets bleus futuristes et un design minimaliste.',
            '42.99',
            18,
            ProductStatus::DISPONIBLE,
            'category_melee',
            [
                ['https://media.valorant-api.com/weaponskins/46664f5b-49ca-3e09-4fe5-56bdef536335/displayicon.png', 'Ion Energy Sword']
            ]
        );

        $this->createProduct(
            $manager,
            'Glitchpop Dagger',
            'Le poignard Glitchpop avec un style cyberpunk néon unique.',
            '37.99',
            22,
            ProductStatus::DISPONIBLE,
            'category_melee',
            [
                ['https://media.valorant-api.com/weaponskins/ddc025b2-475f-889a-2800-80b4215582bc/displayicon.png', 'Glitchpop Dagger']
            ]
        );

        $this->createProduct(
            $manager,
            'Elderflame Dagger',
            'Le poignard Elderflame avec des effets de dragon et de feu.',
            '41.99',
            16,
            ProductStatus::DISPONIBLE,
            'category_melee',
            [
                ['https://media.valorant-api.com/weaponskins/94b40026-4efb-39ea-69d7-fca60be39c56/displayicon.png', 'Elderflame Dagger']
            ]
        );

        $this->createProduct(
            $manager,
            'Player Card Champions 2023',
            'Carte de joueur exclusive Champions 2023 avec design premium doré.',
            '6.99',
            150,
            ProductStatus::DISPONIBLE,
            'category_player_cards',
            [
                ['https://media.valorant-api.com/playercards/2950e0cb-414f-09b6-0dbe-45b3595d82e0/largeart.png', 'Player Card Champions 2023']
            ]
        );

        $this->createProduct(
            $manager,
            'Player Card Champions 2022',
            'Carte de joueur exclusive Champions 2022 avec design premium.',
            '5.99',
            120,
            ProductStatus::DISPONIBLE,
            'category_player_cards',
            [
                ['https://media.valorant-api.com/playercards/8d3a3c3b-44fc-0257-4f3e-e894340eb40a/largeart.png', 'Player Card Champions 2022']
            ]
        );

        $this->createProduct(
            $manager,
            'Player Card Champions 2024',
            'Carte de joueur exclusive Champions 2024 avec design premium.',
            '7.99',
            100,
            ProductStatus::EN_PRECOMMANDE,
            'category_player_cards',
            [
                ['https://media.valorant-api.com/playercards/5e86e82b-443c-e969-ae13-f4be9e085f95/largeart.png', 'Player Card Champions 2024']
            ]
        );

        $manager->flush();
    }

    private function createProduct(
        ObjectManager $manager,
        string $name,
        string $description,
        string $price,
        int $stock,
        ProductStatus $status,
        string $categoryReference,
        array $images
    ): void {
        $product = new Product();
        $product->setName($name);
        $product->setSlug(Product::generateSlug($name));
        $product->setDescription($description);
        $product->setPrice($price);
        $product->setStock($stock);
        $product->setStatus($status);
        $product->setCategory($this->getReference($categoryReference, \App\Entity\Category::class));

        foreach ($images as $imageData) {
            $image = new Image();
            $image->setUrl($imageData[0]);
            $image->setAltText($imageData[1]);
            $product->addImage($image);
        }

        $manager->persist($product);
        $this->addReference('product_' . $this->productIndex, $product);
        $this->productIndex++;
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }
}
