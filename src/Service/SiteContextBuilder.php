<?php

namespace App\Service;

use App\Enum\PagePlacement;
use App\Repository\PageRepository;
use App\Repository\SeasonRepository;
use App\Repository\SocialLinkRepository;
use Symfony\Bundle\SecurityBundle\Security;

class SiteContextBuilder
{
    public function __construct(
        private readonly SeasonRepository $seasonRepository,
        private readonly PageRepository $pageRepository,
        private readonly SocialLinkRepository $socialLinkRepository,
        private readonly Security $security,
    ) {
    }

    public function build(): array
    {
        $currentSeason = $this->seasonRepository->findCurrentSeason();
        $archives = $this->seasonRepository->findArchives();
        $user = $this->security->getUser();

        return [
            'site' => [
                'name' => 'Cécifoot La Bassée',
                'current_season' => $currentSeason,
                'archives' => $archives,
                'header_pages' => $this->pageRepository->findForPlacement(PagePlacement::Header),
                'footer_pages' => $this->pageRepository->findForPlacement(PagePlacement::Footer),
                'social_links' => $this->socialLinkRepository->findVisibleOrdered(),
                'account' => [
                    'is_authenticated' => null !== $user,
                    'user' => $user,
                    'dashboard_path' => null !== $user ? '/compte' : null,
                ],
            ],
        ];
    }
}
