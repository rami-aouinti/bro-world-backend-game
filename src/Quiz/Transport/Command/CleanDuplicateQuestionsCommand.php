<?php

namespace App\Quiz\Transport\Command;

use App\Quiz\Domain\Entity\Question;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:clean-duplicate-questions',
    description: 'Supprime les questions (et réponses) dupliquées.',
)]
class CleanDuplicateQuestionsCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repo = $this->em->getRepository(Question::class);
        $allQuestions = $repo->findAll();

        $seen = [];
        $duplicates = [];

        foreach ($allQuestions as $question) {
            $text = mb_strtolower(trim($question->getQuestion())); // normalisation
            if (isset($seen[$text])) {
                $duplicates[] = $question;
            } else {
                $seen[$text] = true;
            }
        }

        foreach ($duplicates as $q) {
            $output->writeln("🗑️ Suppression : " . $q->getQuestion());
            $this->em->remove($q);
        }

        $this->em->flush();

        $output->writeln("<info>✔ Nettoyage terminé. " . count($duplicates) . " doublons supprimés.</info>");
        return Command::SUCCESS;
    }
}
