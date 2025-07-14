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

class TriviaFixtures extends Fixture
{
    private const array LEVELS = ['easy', 'medium', 'hard'];
    private const int QUESTIONS_PER_COMBO = 10; // Pas trop pour éviter le 429

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function load(ObjectManager $manager): void
    {
        $client = HttpClient::create();

        $categories = $this->fetchTriviaCategories($client);

        foreach (self::LEVELS as $difficulty) {
            foreach ($categories as $apiCategoryId => $categoryName) {
                $level = $this->getOrCreateLevel($manager, ucfirst($difficulty));
                $category = $this->getOrCreateCategory($manager, $categoryName);

                try {
                    $url = sprintf('https://opentdb.com/api.php?amount=%d&category=%d&difficulty=%s&type=multiple', self::QUESTIONS_PER_COMBO, $apiCategoryId, $difficulty);
                    $response = $client->request('GET', $url);
                    $data = $response->toArray();

                    if (empty($data['results'])) {
                        continue;
                    }

                    foreach ($data['results'] as $item) {
                        $question = new Question();
                        $question->setQuestion(html_entity_decode($item['question']));
                        $question->setCategory($category);
                        $question->setLevel($level);
                        $manager->persist($question);

                        $correct = html_entity_decode($item['correct_answer']);
                        $incorrects = array_map('html_entity_decode', $item['incorrect_answers']);
                        $answers = array_merge($incorrects, [$correct]);
                        shuffle($answers);

                        foreach ($answers as $answerText) {
                            $answer = new Answer();
                            $answer->setAnswer($answerText);
                            $answer->setIsTrue($answerText === $correct);
                            $answer->setQuestionId($question);
                            $manager->persist($answer);
                        }
                    }

                    sleep(3); // délai léger pour éviter le rate limit

                } catch (TransportExceptionInterface $e) {
                    echo "Erreur HTTP : " . $e->getMessage();
                    continue;
                }
            }
        }

        $manager->flush();
    }

    /**
     * Récupère la liste des catégories depuis l'API OpenTDB.
     */
    private function fetchTriviaCategories($client): array
    {
        $response = $client->request('GET', 'https://opentdb.com/api_category.php');
        $data = $response->toArray();

        $categories = [];
        foreach ($data['trivia_categories'] as $cat) {
            $categories[$cat['id']] = html_entity_decode($cat['name']);
        }

        return $categories;
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
