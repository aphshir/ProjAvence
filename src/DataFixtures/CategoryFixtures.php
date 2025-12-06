<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $weapons = new Category();
        $weapons->setName('Armes');
        $weapons->setDescription('Collection de skins pour toutes les armes Valorant');
        $manager->persist($weapons);
        $this->addReference('category_weapons', $weapons);

        $agents = new Category();
        $agents->setName('Agents');
        $agents->setDescription('Skins et cosmétiques pour les agents Valorant');
        $manager->persist($agents);
        $this->addReference('category_agents', $agents);

        $accessories = new Category();
        $accessories->setName('Accessoires');
        $accessories->setDescription('Accessoires et cosmétiques divers');
        $manager->persist($accessories);
        $this->addReference('category_accessories', $accessories);

        $bundles = new Category();
        $bundles->setName('Bundles');
        $bundles->setDescription('Collections complètes et bundles premium');
        $manager->persist($bundles);
        $this->addReference('category_bundles', $bundles);

        $rifles = new Category();
        $rifles->setName('Fusils');
        $rifles->setDescription('Skins pour fusils d\'assaut (Vandal, Phantom, etc.)');
        $rifles->setParent($weapons);
        $manager->persist($rifles);
        $this->addReference('category_rifles', $rifles);

        $pistols = new Category();
        $pistols->setName('Pistolets');
        $pistols->setDescription('Skins pour armes de poing (Ghost, Sheriff, etc.)');
        $pistols->setParent($weapons);
        $manager->persist($pistols);
        $this->addReference('category_pistols', $pistols);

        $snipers = new Category();
        $snipers->setName('Snipers');
        $snipers->setDescription('Skins pour fusils de précision (Operator, Marshal, etc.)');
        $snipers->setParent($weapons);
        $manager->persist($snipers);
        $this->addReference('category_snipers', $snipers);

        $smgs = new Category();
        $smgs->setName('SMGs');
        $smgs->setDescription('Skins pour mitraillettes (Spectre, Stinger, etc.)');
        $smgs->setParent($weapons);
        $manager->persist($smgs);
        $this->addReference('category_smgs', $smgs);

        $shotguns = new Category();
        $shotguns->setName('Fusils à pompe');
        $shotguns->setDescription('Skins pour fusils à pompe (Judge, Bucky)');
        $shotguns->setParent($weapons);
        $manager->persist($shotguns);
        $this->addReference('category_shotguns', $shotguns);

        $heavies = new Category();
        $heavies->setName('Armes lourdes');
        $heavies->setDescription('Skins pour mitrailleuses lourdes (Odin, Ares)');
        $heavies->setParent($weapons);
        $manager->persist($heavies);
        $this->addReference('category_heavies', $heavies);

        $melee = new Category();
        $melee->setName('Corps à corps');
        $melee->setDescription('Skins pour armes de mêlée et couteaux');
        $melee->setParent($weapons);
        $manager->persist($melee);
        $this->addReference('category_melee', $melee);

        $duelists = new Category();
        $duelists->setName('Duellistes');
        $duelists->setDescription('Skins pour agents duellistes (Jett, Reyna, Phoenix, etc.)');
        $duelists->setParent($agents);
        $manager->persist($duelists);
        $this->addReference('category_duelists', $duelists);

        $controllers = new Category();
        $controllers->setName('Contrôleurs');
        $controllers->setDescription('Skins pour agents contrôleurs (Brimstone, Omen, Viper, etc.)');
        $controllers->setParent($agents);
        $manager->persist($controllers);
        $this->addReference('category_controllers', $controllers);

        $initiators = new Category();
        $initiators->setName('Initiateurs');
        $initiators->setDescription('Skins pour agents initiateurs (Sova, Breach, Skye, etc.)');
        $initiators->setParent($agents);
        $manager->persist($initiators);
        $this->addReference('category_initiators', $initiators);

        $sentinels = new Category();
        $sentinels->setName('Sentinelles');
        $sentinels->setDescription('Skins pour agents sentinelles (Sage, Cypher, Killjoy, etc.)');
        $sentinels->setParent($agents);
        $manager->persist($sentinels);
        $this->addReference('category_sentinels', $sentinels);

        $playerCards = new Category();
        $playerCards->setName('Player Cards');
        $playerCards->setDescription('Cartes de joueur personnalisées');
        $playerCards->setParent($accessories);
        $manager->persist($playerCards);
        $this->addReference('category_player_cards', $playerCards);

        $sprays = new Category();
        $sprays->setName('Sprays');
        $sprays->setDescription('Sprays et graffitis personnalisés');
        $sprays->setParent($accessories);
        $manager->persist($sprays);
        $this->addReference('category_sprays', $sprays);

        $gunBuddies = new Category();
        $gunBuddies->setName('Gun Buddies');
        $gunBuddies->setDescription('Porte-bonheur pour armes');
        $gunBuddies->setParent($accessories);
        $manager->persist($gunBuddies);
        $this->addReference('category_gun_buddies', $gunBuddies);

        $titles = new Category();
        $titles->setName('Titres');
        $titles->setDescription('Titres de joueur personnalisés');
        $titles->setParent($accessories);
        $manager->persist($titles);
        $this->addReference('category_titles', $titles);

        $premiumBundles = new Category();
        $premiumBundles->setName('Bundles Premium');
        $premiumBundles->setDescription('Collections premium complètes');
        $premiumBundles->setParent($bundles);
        $manager->persist($premiumBundles);
        $this->addReference('category_premium_bundles', $premiumBundles);

        $battlePass = new Category();
        $battlePass->setName('Battle Pass');
        $battlePass->setDescription('Collections du Battle Pass');
        $battlePass->setParent($bundles);
        $manager->persist($battlePass);
        $this->addReference('category_battle_pass', $battlePass);

        $manager->flush();
    }
}
