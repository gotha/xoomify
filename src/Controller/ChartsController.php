<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ArtistRepository;
use App\Repository\TrackRepository;
use App\Repository\UserRepository;
use App\Service\ChartsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ChartsController extends AbstractController
{
    #[Route('/charts/songs/{period}/{user_uri}', defaults: ['period' => 'week'], name: 'app_song_chart')]
    public function charts(
        #[CurrentUser] User $currentUser,
        UserRepository $userRepository,
        ChartsService $chartsService,
        ChartPeriod $period = ChartPeriod::Week,
        string $user_uri = null,
    ): Response {
        list($startDate, $endDate) = $period->getDates();

        $user = null;
        if ($user_uri) {
            $user = ('me' == $user_uri)
                ? $currentUser
                : $userRepository->findOneBy(['id' => (int) $user_uri]);
            if (!$user) {
                throw new NotFoundHttpException('unable to find user');
            }
        }

        $tracksChart = $chartsService->getMostListenedTracks($startDate, $endDate, $user);

        return $this->render('charts/songs.html.twig', [
            'user' => $user,
            'period' => $period->value,
            'chart' => $tracksChart,
            'title' => ($user) ? $user->getName().'\'s charts ' : null,
            'page' => ($user && $user->getId() == $currentUser->getId()) ? 'me' : 'home',
        ]);
    }

    #[Route('/charts/artists/{period}/{user_uri}', defaults: ['period' => 'week'], name: 'app_artists_chart')]
    public function artistsCharts(
        #[CurrentUser] User $currentUser,
        UserRepository $userRepository,
        ChartsService $chartsService,
        ChartPeriod $period = ChartPeriod::Week,
        string $user_uri = null,
    ): Response {
        list($startDate, $endDate) = $period->getDates();

        $user = null;
        if ($user_uri) {
            $user = ('me' == $user_uri)
                ? $currentUser
                : $userRepository->findOneBy(['id' => (int) $user_uri]);
            if (!$user) {
                throw new NotFoundHttpException('unable to find user');
            }
        }

        $chart = $chartsService->getMostListenedArtists($startDate, $endDate, $user);

        return $this->render('charts/artists.html.twig', [
            'user' => $user,
            'period' => $period->value,
            'chart' => $chart,
            'title' => ($user) ? $user->getName().'\'s charts ' : null,
            'page' => ($user && $user->getId() == $currentUser->getId()) ? 'me' : 'home',
        ]);
    }

    #[Route('/charts/track/{trackId}/{period}', defaults: ['period' => 'week'], name: 'app_track_listeners')]
    public function trackListeners(
        TrackRepository $trackRepository,
        ChartsService $chartsService,
        string $trackId,
        ChartPeriod $period = ChartPeriod::Week,
    ): Response {
        $track = $trackRepository->findOneBy(['id' => $trackId]);
        if (!$track) {
            throw new NotFoundHttpException('track not found');
        }

        list($startDate, $endDate) = $period->getDates();
        $chart = $chartsService->getTrackListeners($trackId, $startDate, $endDate);

        return $this->render('charts/song.html.twig', [
            'track' => $track,
            'period' => $period->value,
            'chart' => $chart,
            'page' => 'track_listeners',
        ]);
    }

    #[Route('/charts/artist/{artistId}/{period}', defaults: ['period' => 'week'], name: 'app_artist_listeners')]
    public function artistListeners(
        ArtistRepository $artistRepository,
        ChartsService $chartsService,
        string $artistId,
        ChartPeriod $period = ChartPeriod::Week,
    ): Response {
        $artist = $artistRepository->findOneBy(['id' => $artistId]);
        if (!$artist) {
            throw new NotFoundHttpException('artist not found');
        }

        list($startDate, $endDate) = $period->getDates();
        $chart = $chartsService->getArtistListeners($artistId, $startDate, $endDate);

        return $this->render('charts/artist.html.twig', [
            'artist' => $artist,
            'period' => $period->value,
            'chart' => $chart,
            'page' => 'artist_listeners',
        ]);
    }
}

enum ChartPeriod: string
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';

    /*
     * @return array[\DateTime, \DateTime]
     */
    public function getDates(): array
    {
        $startDate = new \DateTime();
        $endDate = new \DateTime();
        switch ($this) {
            case ChartPeriod::Day:
                $startDate->sub(new \DateInterval('P1D'));
                break;
            case ChartPeriod::Week:
                $startDate->sub(new \DateInterval('P7D'));
                break;
            case ChartPeriod::Month:
                $startDate->sub(new \DateInterval('P30D'));
                break;
            case ChartPeriod::Year:
                $startDate->sub(new \DateInterval('P1Y'));
                break;
        }

        return [$startDate, $endDate];
    }
}
