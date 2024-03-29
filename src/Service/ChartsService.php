<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\ArtistRepository;
use App\Repository\TrackRepository;
use App\Repository\UserRepository;
use App\Service\Charts\ArtistsChart;
use App\Service\Charts\ArtistsChartItem;
use App\Service\Charts\TracksChart;
use App\Service\Charts\TracksChartItem;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;

class ChartsService
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected ArtistRepository $artistRepository,
        protected TrackRepository $trackRepository,
        protected UserRepository $userRepository,
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

    /**
     * @return array<int, User>
     */
    public function getTrackListeners(
        int $trackId,
        \DateTime $startDate,
        \DateTime $endDate,
        int $limit = 20,
    ): array {
        $sql = '
			SELECT uph.user_id, count(uph.id) as num
			FROM user_play_history uph
			WHERE track_id  = :trackId
			AND played_at >= :startDate
			AND played_at <= :endDate
			GROUP BY user_id
			ORDER BY num desc
        ';

        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindValue('trackId', $trackId, ParameterType::INTEGER);
        $stmt->bindValue('startDate', $startDate->format('Y-m-d H:i:s'));
        $stmt->bindValue('endDate', $endDate->format('Y-m-d H:i:s'));
        $res = $stmt->executeQuery();

        $data = $res->fetchAllAssociative();
        $userPlays = [];
        foreach ($data as $i) {
            $userPlays[$i['user_id']] = $i['num'];
        }
        $userIds = array_keys($userPlays);

        $users = $this->userRepository->findBy(['id' => $userIds]);
        $retval = [];
        foreach ($users as $user) {
            $plays = $userPlays[$user->getId()];
            $retval[$plays] = $user;
        }
        krsort($retval);

        return $retval;
    }

    /**
     * @return array<int, User>
     */
    public function getArtistListeners(
        int $artistId,
        \DateTime $startDate,
        \DateTime $endDate,
        int $limit = 20,
    ): array {
        $sql = '
			SELECT user_id, COUNT(*) as num
			FROM user_play_history uph
			JOIN track t
				ON  t.id  = uph.track_id
			JOIN track_artist ta
				ON ta.track_id  = t.id
			WHERE ta.artist_id = :artistId
			AND uph.played_at >= :startDate
			AND uph.played_at <= :endDate
			GROUP BY uph.user_id
			ORDER BY num DESC
		';
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindValue('artistId', $artistId, ParameterType::INTEGER);
        $stmt->bindValue('startDate', $startDate->format('Y-m-d H:i:s'));
        $stmt->bindValue('endDate', $endDate->format('Y-m-d H:i:s'));
        $res = $stmt->executeQuery();

        $data = $res->fetchAllAssociative();
        $userPlays = [];
        foreach ($data as $i) {
            $userPlays[$i['user_id']] = $i['num'];
        }
        $userIds = array_keys($userPlays);

        $users = $this->userRepository->findBy(['id' => $userIds]);
        $retval = [];
        foreach ($users as $user) {
            $plays = $userPlays[$user->getId()];
            $retval[$plays] = $user;
        }
        krsort($retval);

        return $retval;
    }
}
