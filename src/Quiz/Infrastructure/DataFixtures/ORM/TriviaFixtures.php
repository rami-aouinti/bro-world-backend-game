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
            $response = $client->request('GET', "https://opentdb.com/api.php?amount=50&difficulty=$difficulty&type=multiple");
            $data = $response->toArray();

            $level = $this->getOrCreateLevel($manager, ucfirst($difficulty));

            foreach ($data['results'] as $item) {
                $translatedQuestion = $this->translate($item['question']);
                $translatedCorrect = $this->translate($item['correct_answer']);
                $translatedIncorrects = array_map([$this, 'translate'], $item['incorrect_answers']);

                $categoryName = html_entity_decode($item['category']);
                $category = $this->getOrCreateCategory($manager, $categoryName);

                $question = new Question();
                $question->setQuestion($translatedQuestion);
                $question->setCategory($category);
                $question->setLevel($level);
                $manager->persist($question);

                $allAnswers = array_merge($translatedIncorrects, [$translatedCorrect]);
                shuffle($allAnswers);

                foreach ($allAnswers as $text) {
                    $answer = new Answer();
                    $answer->setAnswer($text);
                    $answer->setIsTrue($text === $translatedCorrect);
                    $answer->setQuestionId($question);
                    $manager->persist($answer);
                }
            }
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

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    private function translate(string $text): string
    {
        $client = HttpClient::create();
        $response = $client->request('POST', 'https://libretranslate.de/translate', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'q' => html_entity_decode($text),
                'source' => 'en',
                'target' => 'fr',
                'format' => 'text',
            ],
        ]);

        $data = $response->toArray();

        return $data['translatedText'] ?? $text;
    }
}
