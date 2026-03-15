<?php

namespace App\Service;

use App\Entity\TeamIdentity;
use App\Repository\TeamIdentityRepository;

class TeamBranding
{
    /**
     * @var array<string, string>
     */
    private const FALLBACK_LOGOS = [
        'cecifoot 59 la bassée' => 'assets/teams/cecifoot-la-bassee.svg',
        'cecifoot la bassée' => 'assets/teams/cecifoot-la-bassee.svg',
        'fc cecifoot 59 la bassée' => 'assets/teams/cecifoot-la-bassee.svg',
        'rc lens' => 'assets/teams/rc-lens-official.png',
        'rc lens cécifoot' => 'assets/teams/rc-lens-official.png',
        'fc nantes' => 'assets/teams/fc-nantes-official.svg',
        'fc nantes cécifoot' => 'assets/teams/fc-nantes-official.svg',
        'as saint-mandé' => 'assets/teams/as-saint-mande-official.jpg',
        'as saint-mandé cécifoot' => 'assets/teams/as-saint-mande-official.jpg',
        'clermont jokers' => 'assets/teams/clermont-jokers-official.jpg',
    ];

    public function __construct(private readonly TeamIdentityRepository $teamIdentityRepository)
    {
    }

    public function getLogoPath(?string $teamName): string
    {
        $normalized = $this->normalize($teamName);
        $teamIdentity = $this->teamIdentityRepository->findMatchingIdentity($normalized);

        if ($teamIdentity instanceof TeamIdentity && $teamIdentity->getLogoPath()) {
            return $this->resolveStoredPath($teamIdentity);
        }

        return self::FALLBACK_LOGOS[$normalized] ?? 'assets/teams/default-team.svg';
    }

    private function resolveStoredPath(TeamIdentity $teamIdentity): string
    {
        $path = (string) $teamIdentity->getLogoPath();

        if (str_starts_with($path, 'assets/') || str_starts_with($path, 'uploads/')) {
            return $path;
        }

        return 'uploads/team-identities/'.$path;
    }

    private function normalize(?string $teamName): string
    {
        $teamName = trim(mb_strtolower((string) $teamName));
        $teamName = str_replace(['’', '\''], ' ', $teamName);

        return preg_replace('/\s+/', ' ', $teamName) ?? '';
    }
}
