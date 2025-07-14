<?php

namespace App\Quiz\Infrastructure\DataFixtures\ORM;

use App\Quiz\Domain\Entity\Answer;
use App\Quiz\Domain\Entity\Category;
use App\Quiz\Domain\Entity\Level;
use App\Quiz\Domain\Entity\Question;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Random\RandomException;
use Faker\Factory;

class CategoryLevelFixtures extends Fixture
{
    /**
     * @throws RandomException
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        // Create Categories
        $categories = ['Culture', 'Science', 'Geography', 'History'];
        $categoryEntities = [];

        foreach ($categories as $cat) {
            $category = new Category();
            $category->setName($cat);
            $manager->persist($category);
            $categoryEntities[] = $category;
        }

        // Create Levels
        $levels = ['Easy', 'Medium', 'Hard'];
        $levelEntities = [];

        foreach ($levels as $label) {
            $level = new Level();
            $level->setLabel($label);
            $manager->persist($level);
            $levelEntities[] = $level;
        }

        // Create Questions and Answers
        for ($i = 1; $i <= 20; $i++) {
            $question = new Question();
            $question->setQuestion($faker->sentence(6));
            $question->setCategory($faker->randomElement($categoryEntities));
            $question->setLevel($faker->randomElement($levelEntities));
            $manager->persist($question);

            $correctAnswerIndex = random_int(0, 3);
            for ($j = 0; $j < 4; $j++) {
                $answer = new Answer();
                $answer->setAnswer($faker->word());
                $answer->setIsTrue($j === $correctAnswerIndex);
                $answer->setQuestionId($question);
                $manager->persist($answer);
            }
        }

        $manager->flush();
    }
}
