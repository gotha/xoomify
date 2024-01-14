<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserPlayHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserPlayHistory>
 *
 * @method UserPlayHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserPlayHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserPlayHistory[]    findAll()
 * @method UserPlayHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserPlayHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPlayHistory::class);
    }

    public function getUserLatestPlay(User $user): UserPlayHistory|null
    {
        return $this->findOneBy([
            'user' => $user,
        ], [
            'playedAt' => 'desc',
        ]);
    }
}
