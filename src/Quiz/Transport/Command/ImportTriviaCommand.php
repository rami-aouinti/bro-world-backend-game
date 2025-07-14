<?php

namespace App\Quiz\Transport\Command;


use App\Quiz\Domain\Entity\Answer;
use App\Quiz\Domain\Entity\Category;
use App\Quiz\Domain\Entity\Level;
use App\Quiz\Domain\Entity\Question;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

#[AsCommand(
    name: 'app:import-trivia',
    description: 'Import trivia questions from OpenTDB API',
)]
class ImportTriviaCommand extends Command
{
    private const array LEVELS = ['easy', 'medium', 'hard'];
    private const int QUESTIONS_PER_COMBO = 10;

    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    /**
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = HttpClient::create();
        $output->writeln('<info>Fetching categories...</info>');
        $categories = $this->fetchTriviaCategories($client);

        foreach (self::LEVELS as $difficulty) {
            foreach ($categories as $categoryId => $categoryName) {
                $output->writeln("Importing <comment>$difficulty</comment> questions for category <info>$categoryName</info>");

                $level = $this->getOrCreateLevel(ucfirst($difficulty));
                $category = $this->getOrCreateCategory($categoryName);

                try {
                    $url = sprintf('https://opentdb.com/api.php?amount=%d&category=%d&difficulty=%s&type=multiple', self::QUESTIONS_PER_COMBO, $categoryId, $difficulty);
                    $response = $client->request('GET', $url);
                    $data = $response->toArray();

                    if (empty($data['results'])) {
                        $output->writeln("<comment>No questions found for this category/difficulty.</comment>");
                        continue;
                    }

                    foreach ($data['results'] as $item) {
                        $question = new Question();
                        $question->setQuestion(html_entity_decode($item['question']));
                        $question->setCategory($category);
                        $question->setLevel($level);
                        $this->em->persist($question);

                        $correct = html_entity_decode($item['correct_answer']);
                        $incorrects = array_map('html_entity_decode', $item['incorrect_answers']);
                        $answers = array_merge($incorrects, [$correct]);
                        shuffle($answers);

                        foreach ($answers as $text) {
                            $answer = new Answer();
                            $answer->setAnswer($text);
                            $answer->setIsTrue($text === $correct);
                            $answer->setQuestionId($question);
                            $this->em->persist($answer);
                        }
                    }

                    $this->em->flush();
                    sleep(1); // Evite l'erreur 429

                } catch (TransportExceptionInterface $e) {
                    $output->writeln("<error>HTTP error: {$e->getMessage()}</error>");
                    continue;
                }
            }
        }

        $output->writeln('<info>Import completed successfully!</info>');
        return Command::SUCCESS;
    }

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

    private function getOrCreateCategory(string $name): Category
    {
        $repo = $this->em->getRepository(Category::class);
        $category = $repo->findOneBy(['name' => $name]);

        if (!$category) {
            $category = new Category();
            $category->setName($name);
            $this->em->persist($category);
            $this->em->flush();
        }

        return $category;
    }

    private function getOrCreateLevel(string $label): Level
    {
        $repo = $this->em->getRepository(Level::class);
        $level = $repo->findOneBy(['label' => $label]);

        if (!$level) {
            $level = new Level();
            $level->setLabel($label);
            $this->em->persist($level);
            $this->em->flush();
        }

        return $level;
    }
}
