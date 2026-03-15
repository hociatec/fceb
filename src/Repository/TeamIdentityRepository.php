<?php

namespace App\Repository;

use App\Entity\TeamIdentity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamIdentity>
 */
class TeamIdentityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamIdentity::class);
    }

    /**
     * @return TeamIdentity[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.teamName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findMatchingIdentity(string $normalizedTeamName): ?TeamIdentity
    {
        foreach ($this->findAllOrdered() as $teamIdentity) {
            $variants = [$teamIdentity->getTeamName()];
            if ($teamIdentity->getAliases()) {
                $variants = [...$variants, ...preg_split('/\R+/', $teamIdentity->getAliases())];
            }

            foreach ($variants as $variant) {
                if ($this->normalize($variant) === $normalizedTeamName) {
                    return $teamIdentity;
                }
            }
        }

        return null;
    }

    private function normalize(?string $teamName): string
    {
        $teamName = trim(mb_strtolower((string) $teamName));
        $teamName = str_replace(['’', '\''], ' ', $teamName);

        return preg_replace('/\s+/', ' ', $teamName) ?? '';
    }
}
