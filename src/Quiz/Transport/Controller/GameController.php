<?php

declare(strict_types=1);

namespace App\Quiz\Transport\Controller;

use App\Quiz\Application\Service\ScoreLifecycleService;
use App\Quiz\Domain\Entity\Game;
use App\Quiz\Domain\Entity\GameQuestion;
use App\Quiz\Domain\Entity\Score;
use App\Quiz\Infrastructure\Repository\AnswerRepository;
use App\Quiz\Infrastructure\Repository\GameQuestionRepository;
use App\Quiz\Infrastructure\Repository\GameRepository;
use App\Quiz\Infrastructure\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    #[Route('/game', name: 'app_game_create')]
    public function createGame(QuestionRepository $questionRepository, EntityManagerInterface $entityManager): Response
    {
        $questions = $questionRepository->findAll();
        shuffle($questions);
        $questions = array_slice($questions, 0, 3);

        $user = $this->getUser();
        $game = new Game();
        $game->setUserId($user);

        $score = new Score();
        $score->setGame($game);
        $score->setScore('0');

        foreach ($questions as $question) {
            $gameQuestion = new GameQuestion();
            $gameQuestion->setGame($game);
            $gameQuestion->setQuestion($question);
            $gameQuestion->setIsResponse(false);

            $entityManager->persist($gameQuestion);
        }

        $entityManager->persist($game);
        $entityManager->flush();

        return $this->redirectToRoute('app_game', ['id' => $game->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/game/{id}', name: 'app_game')]
    public function show(string $id, GameRepository $gameRepository): Response
    {
        $game = $gameRepository->find($id);

        return $this->render('game/index.html.twig', [
            'game' => $game,
        ]);
    }

    #[Route('/game/{gameId}/{answerId}', name: 'game_question_response')]
    public function handleAnswer(
        string $gameId,
        string $answerId,
        GameRepository $gameRepository,
        AnswerRepository $answerRepository,
        GameQuestionRepository $gameQuestionRepository,
        ScoreLifecycleService $scoreLifecycleService
    ): Response {
        $answer = $answerRepository->find($answerId);
        $game = $gameRepository->find($gameId);

        if (!$answer || !$game) {
            return $this->redirectToRoute('app_game', ['id' => $gameId], Response::HTTP_SEE_OTHER);
        }

        if ($answer->isIsTrue()) {
            $score = $game->getScore();
            if ($score !== null) {
                $scoreLifecycleService->incrementScore($score->getId(), 1);
            }
        }

        $gameQuestion = $answer->getQuestionId()->getGameQuestions()->filter(
            static function (GameQuestion $gameQuestion) use ($game) {
                return $gameQuestion->getGame() === $game;
            }
        )->first();

        if ($gameQuestion) {
            $gameQuestion->setIsResponse(true);
            $gameQuestionRepository->save($gameQuestion, true);
        }

        return $this->redirectToRoute('app_game', ['id' => $gameId], Response::HTTP_SEE_OTHER);
    }
}
