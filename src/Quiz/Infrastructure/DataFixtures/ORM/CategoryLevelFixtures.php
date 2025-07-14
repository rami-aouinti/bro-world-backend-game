<?php

namespace App\Quiz\Infrastructure\DataFixtures\ORM;

use App\Quiz\Domain\Entity\Category;
use App\Quiz\Domain\Entity\Level;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryLevelFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            'Culture', 'Sciences', 'Sport', 'Histoire', 'Géographie', 'Technologie', 'Langues'
        ];

        foreach ($categories as $catName) {
            $category = new Category();
            $category->setName($catName);
            $manager->persist($category);
        }

        $levels = [
            'Facile', 'Moyen', 'Difficile'
        ];

        foreach ($levels as $label) {
            $level = new Level();
            $level->setLabel($label);
            $manager->persist($level);
        }

        $manager->flush();
    }
}
