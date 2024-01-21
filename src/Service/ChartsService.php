<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\ArtistRepository;
use App\Repository\TrackRepository;
use App\Service\Charts\ArtistsChart;
use App\Service\Charts\ArtistsChartItem;
use App\Service\Charts\TracksChart;
use App\Service\Charts\TracksChartItem;
use Doctrine\ORM\EntityManagerInterface;

class ChartsService
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected ArtistRepository $artistRepository,
        protected TrackRepository $trackRepository,
    ) {
    }

    public function getMostListenedTracks(
        \DateTime $startDate,
        \DateTime $endDate,
        User $user = null,
        int $limit = 20,
    ): TracksChart {
        $userFilter = '';
        if ($user) {
            $userFilter = 'AND uph.user_id = :user_id';
        }
        $sql = '
			SELECT uph.track_id, COUNT(uph.track_id) as num
			FROM user_play_history uph
			WHERE uph.played_at >= :start_date
			AND uph.played_at <= :end_date
			'.$userFilter.'
			GROUP BY uph.track_id
			ORDER BY num DESC
			LIMIT '.$limit.'
		';

        $stmt = $this->em->getConnection()->prepare($sql);
        $params = [
            'start_date' => $startDate->format('Y-m-d H:i:s'),
            'end_date' => $endDate->format('Y-m-d H:i:s'),
        ];
        if ($user) {
            $params['user_id'] = $user->getId();
        }

        $res = $stmt->executeQuery($params);
        $data = $res->fetchAllAssociative();
        $trackPlaysMap = [];
        foreach ($data as $i) {
            $trackPlaysMap[$i['track_id']] = $i['num'];
        }
        $trackIds = array_keys($trackPlaysMap);
        $tracks = $this->trackRepository->findBy(['id' => $trackIds]);

        $items = [];
        foreach ($tracks as $track) {
            $items[] = new TracksChartItem($track, $trackPlaysMap[$track->getId()]);
        }

        usort($items, fn (TracksChartItem $a, TracksChartItem $b) => $a->getNumPlays() < $b->getNumPlays());

        return new TracksChart($items);
    }

    public function getMostListenedArtists(
        \DateTime $startDate,
        \DateTime $endDate,
        User $user = null,
        int $limit = 20,
    ): ArtistsChart {
        $userFilter = '';
        if ($user) {
            $userFilter = 'AND uph.user = :user';
        }

        $dql = "
            SELECT a.id, COUNT(a.id) AS num
            FROM App\Entity\UserPlayHistory uph
			JOIN uph.track t
			JOIN t.artists a
            WHERE uph.playedAt >= :startDate
			AND uph.playedAt <= :endDate
			".$userFilter.'
            GROUP BY a.id
            ORDER BY num DESC
		';
        $query = $this->em->createQuery($dql)
                    ->setMaxResults($limit)
                    ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'))
                    ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'));
        if ($user) {
            $query = $query->setParameter('user', $user);
        }
        $res = $query->getResult();

        $artistListens = [];
        foreach ($res as $i) {
            $artistListens[$i['id']] = $i['num'];
        }
        $artistIds = array_keys($artistListens);

        $artists = $this->artistRepository->findBy(['id' => $artistIds]);

        $items = [];
        foreach ($artists as $a) {
            $items[] = new ArtistsChartItem($a, $artistListens[$a->getId()]);
        }

        usort($items, fn (ArtistsChartItem $a, ArtistsChartItem $b) => $a->getNumPlays() < $b->getNumPlays());

        return new ArtistsChart($items);
    }
}
