<?php

namespace App\Controller;

use App\Service\ChartsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChartsController extends AbstractController
{
    #[Route('/charts/songs/{period}', defaults: ['period' => 'week'], name: 'app_song_chart')]
    public function charts(
        ChartsService $chartsService,
        ChartPeriod $period = ChartPeriod::Week,
    ): Response {
        list($startDate, $endDate) = $period->getDates();

        $tracksChart = $chartsService->getMostListenedTracks($startDate, $endDate);

        return $this->render('charts/songs.html.twig', [
            'period' => $period->value,
            'chart' => $tracksChart,
        ]);
    }

    #[Route('/charts/artists/{period}', defaults: ['period' => 'week'], name: 'app_artists_chart')]
    public function artistsCharts(
        ChartsService $chartsService,
        ChartPeriod $period = ChartPeriod::Week,
    ): Response {
        list($startDate, $endDate) = $period->getDates();

        $chart = $chartsService->getMostListenedArtists($startDate, $endDate);

        return $this->render('charts/artists.html.twig', [
            'period' => $period->value,
            'chart' => $chart,
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
