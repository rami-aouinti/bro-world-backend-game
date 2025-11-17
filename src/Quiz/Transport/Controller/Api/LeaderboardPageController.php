<?php

declare(strict_types=1);

namespace App\Quiz\Transport\Controller\Api;

use App\Quiz\Application\Service\ScoreLeaderboardQueryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LeaderboardPageController extends AbstractController
{
    #[Route('/scores', name: 'app_leaderboard')]
    public function index(ScoreLeaderboardQueryService $scoreLeaderboardQueryService): Response
    {
        $scores = $scoreLeaderboardQueryService->fetchLeaderboard();

        return $this->render('score/index.html.twig', [
            'scores' => $scores,
        ]);
    }
}
