<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\ArtistRepository;
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
            $userFilter = 'AND uph.user = :user';
        }
        $dql = '
			SELECT uph as item, COUNT(uph.id) as num
			FROM App\Entity\UserPlayHistory uph
			WHERE uph.playedAt >= :startDate
			AND uph.playedAt <= :endDate
			'.$userFilter.'
			GROUP BY uph.track
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

        $items = [];
        foreach ($res as $i) {
            $items[] = new TracksChartItem($i['item']->getTrack(), $i['num']);
        }

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
