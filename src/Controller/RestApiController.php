<?php

namespace App\Controller;

use App\Repository\ArtistRepository;
use App\Repository\TrackRepository;
use App\Repository\UserPlayHistoryRepository;
use App\Repository\UserRepository;
use App\Service\ChartsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class RestApiController extends AbstractController
{
    #[Route('/', name: 'app_rest_api')]
    public function index(): Response
    {
        return new JsonResponse([
            'hello' => 'xoomify',
        ]);
    }

    #[Route('/users', name: 'app_rest_api_users')]
    public function users(
        UserRepository $userRepository,
    ): Response {
        $users = $userRepository->findAll();

        return new JsonResponse($users);
    }

    #[Route('/user/{user_id}', name: 'app_rest_api_user')]
    public function user(
        UserRepository $userRepository,
        int $user_id = null,
    ): Response {
        $user = $userRepository->findOneBy(['id' => (int) $user_id]);

        return new JsonResponse($user);
    }

    #[Route('/user/{user_id}/history', name: 'app_rest_api_user_history')]
    public function userHistory(
        UserPlayHistoryRepository $userPlayHistoryRepository,
        UserRepository $userRepository,
        int $user_id = null,
    ): Response {
        $user = $userRepository->findOneBy(['id' => (int) $user_id]);
        if (!$user) {
            return new JsonResponse([]);
        }
        $history = $userPlayHistoryRepository->findBy([
            'user' => $user,
        ], [
            'playedAt' => 'desc',
        ], 50);

        return new JsonResponse($history);
    }

    #[Route('/artist/{id}', name: 'app_rest_api_artist')]
    public function artist(
        ArtistRepository $artistRepository,
        int $id,
    ): Response {
        $artist = $artistRepository->findOneBy(['id' => $id]);

        return new JsonResponse($artist);
    }

    #[Route('/artist/{id}/listeners', name: 'app_rest_api_artist_listeners')]
    public function artistListeners(
        ArtistRepository $artistRepository,
        ChartsService $chartsService,
        Request $request,
        int $id,
    ): Response {
        $artist = $artistRepository->findOneBy(['id' => $id]);
        if (!$artist) {
            return new JsonResponse([]);
        }
        $range = new ApiRequestDateRange(
            $request->query->get('start_date'),
            $request->query->get('end_date'),
        );

        $chart = $chartsService->getArtistListeners($artist->getId(), $range->getStart(), $range->getEnd());

        return new JsonResponse($chart);
    }

    #[Route('/artists/most_listened', name: 'app_rest_api_artist_most_listened')]
    public function artistsMostListened(
        ChartsService $chartsService,
        Request $request,
    ): Response {
        $range = new ApiRequestDateRange(
            $request->query->get('start_date'),
            $request->query->get('end_date'),
        );
        $chart = $chartsService->getMostListenedArtists($range->getStart(), $range->getEnd());

        return new JsonResponse($chart);
    }

    #[Route('/track/{id}', name: 'app_rest_api_track')]
    public function track(
        TrackRepository $trackRepository,
        int $id,
    ): Response {
        $track = $trackRepository->findOneBy(['id' => (int) $id]);

        return new JsonResponse($track);
    }

    #[Route('/track/{id}/listeners', name: 'app_rest_api_track_listeners')]
    public function trackListeners(
        TrackRepository $trackRepository,
        ChartsService $chartsService,
        Request $request,
        int $id,
    ): Response {
        $track = $trackRepository->findOneBy(['id' => (int) $id]);
        if (!$track) {
            return new JsonResponse([]);
        }

        $range = new ApiRequestDateRange(
            $request->query->get('start_date'),
            $request->query->get('end_date'),
        );

        $chart = $chartsService->getTrackListeners($track->getId(), $range->getStart(), $range->getEnd());

        return new JsonResponse($chart);
    }

    #[Route('/tracks/most_listened', name: 'app_rest_api_tracks_most_listened')]
    public function tracksMostListened(
        ChartsService $chartsService,
        Request $request,
    ): Response {
        $range = new ApiRequestDateRange(
            $request->query->get('start_date'),
            $request->query->get('end_date'),
        );
        $chart = $chartsService->getMostListenedTracks($range->getStart(), $range->getEnd());

        return new JsonResponse($chart);
    }
}

class ApiRequestDateRange
{
    private \DateTime $start;
    private \DateTime $end;

    public function __construct(string $start = null, string $end = null)
    {
        $this->start = (new \DateTime())->sub(new \DateInterval('P30D'));
        if ($start) {
            try {
                $this->start = new \DateTime($start);
            } catch (\Exception $e) {
            }
        }

        $this->end = new \DateTime();
        if ($end) {
            try {
                $this->end = new \DateTime($end);
            } catch (\Exception $e) {
            }
        }

        if ($this->end <= $this->start) {
            throw new \Exception('end date must be after start date');
        }

        $diff = $this->end->diff($this->start);
        if ($diff->days > 366) {
            throw new \Exception('date range cannot be larger than 1 year');
        }
    }

    public function getStart(): \DateTime
    {
        return $this->start;
    }

    public function getEnd(): \DateTime
    {
        return $this->end;
    }
}
