<?php

namespace App\Service;

use App\Entity\User;
use App\Service\Charts\TracksChart;
use App\Service\Charts\TracksChartItem;
use Doctrine\ORM\EntityManagerInterface;

class ChartsService
{
    public function __construct(
        protected EntityManagerInterface $em,
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
			JOIN uph.track as t
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
}
