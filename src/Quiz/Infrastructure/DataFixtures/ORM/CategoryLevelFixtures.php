<?php

namespace App\Quiz\Infrastructure\DataFixtures\ORM;

use App\Quiz\Domain\Entity\Answer;
use App\Quiz\Domain\Entity\Category;
use App\Quiz\Domain\Entity\Game;
use App\Quiz\Domain\Entity\GameQuestion;
use App\Quiz\Domain\Entity\Level;
use App\Quiz\Domain\Entity\Question;
use App\Quiz\Domain\Entity\Score;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Random\RandomException;
use Faker\Factory;
use Ramsey\Uuid\Uuid;

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
        $questionEntities = [];
        foreach ($categoryEntities as $category) {
            foreach ($levelEntities as $level) {
                for ($i = 0; $i < 5; $i++) {
                    $question = new Question();
                    $question->setQuestion($faker->sentence(6));
                    $question->setCategory($category);
                    $question->setLevel($level);
                    $manager->persist($question);

                    $correctAnswerIndex = random_int(0, 3);
                    for ($j = 0; $j < 4; $j++) {
                        $answer = new Answer();
                        $answer->setAnswer($faker->sentence(3));
                        $answer->setIsTrue($j === $correctAnswerIndex);
                        $answer->setQuestionId($question);
                        $manager->persist($answer);
                    }

                    $questionEntities[] = $question;
                }
            }
        }

        // Populate demo games/scores for leaderboard cache testing
        $userIds = [
            '3fa85f64-5717-4562-b3fc-2c963f66afa6',
            'f47ac10b-58cc-4372-a567-0e02b2c3d479',
            '6fa459ea-ee8a-3ca4-894e-db77e160355e',
        ];

        foreach ($userIds as $userId) {
            for ($gameIndex = 0; $gameIndex < 2; $gameIndex++) {
                $game = new Game();
                $score = new Score();
                $score->setScore((string) $faker->numberBetween(10, 30));
                $score->setUser(Uuid::fromString($userId));
                $score->setGame($game);

                $selectedQuestions = $faker->randomElements($questionEntities, 10);
                foreach ($selectedQuestions as $question) {
                    $gameQuestion = new GameQuestion();
                    $gameQuestion->setQuestion($question);
                    $gameQuestion->setIsResponse($faker->boolean(70));
                    $game->addGameQuestion($gameQuestion);
                    $manager->persist($gameQuestion);
                }

                $manager->persist($game);
                $manager->persist($score);
            }
        }

        $manager->flush();
    }
}
