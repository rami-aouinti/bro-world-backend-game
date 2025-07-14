<?php

namespace App\Quiz\Infrastructure\DataFixtures\ORM;

use App\Quiz\Domain\Entity\Answer;
use App\Quiz\Domain\Entity\Category;
use App\Quiz\Domain\Entity\Level;
use App\Quiz\Domain\Entity\Question;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/*
 * This file is part of the Quiz project.
 */
class TriviaFixtures extends Fixture
{
    private const array LEVELS = ['easy', 'medium', 'hard'];

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function load(ObjectManager $manager): void
    {
        $client = HttpClient::create();

        foreach (self::LEVELS as $difficulty) {
            for ($i = 9; $i < 33; $i++) {
                $response = $client->request('GET', "https://opentdb.com/api.php?amount=50&category=$i&difficulty=$difficulty");
                $data = $response->toArray();

                $level = $this->getOrCreateLevel($manager, ucfirst($difficulty));

                foreach ($data['results'] as $item) {
                    $categoryName = html_entity_decode($item['category']);
                    $category = $this->getOrCreateCategory($manager, $categoryName);

                    $question = new Question();
                    $question->setQuestion(html_entity_decode($item['question']));
                    $question->setCategory($category);
                    $question->setLevel($level);
                    $manager->persist($question);

                    // Mix answers
                    $correctAnswer = html_entity_decode($item['correct_answer']);
                    $incorrectAnswers = array_map('html_entity_decode', $item['incorrect_answers']);
                    $allAnswers = array_merge($incorrectAnswers, [$correctAnswer]);
                    shuffle($allAnswers);

                    foreach ($allAnswers as $text) {
                        $answer = new Answer();
                        $answer->setAnswer($text);
                        $answer->setIsTrue($text === $correctAnswer);
                        $answer->setQuestionId($question);
                        $manager->persist($answer);
                    }
                }
                sleep(5);
            }
            sleep(5);
        }

        $manager->flush();
    }

    private function getOrCreateCategory(ObjectManager $manager, string $name): Category
    {
        $repo = $manager->getRepository(Category::class);
        $category = $repo->findOneBy(['name' => $name]);

        if (!$category) {
            $category = new Category();
            $category->setName($name);
            $manager->persist($category);
        }

        return $category;
    }

    private function getOrCreateLevel(ObjectManager $manager, string $label): Level
    {
        $repo = $manager->getRepository(Level::class);
        $level = $repo->findOneBy(['label' => $label]);

        if (!$level) {
            $level = new Level();
            $level->setLabel($label);
            $manager->persist($level);
        }

        return $level;
    }
}
