<?php

namespace App\Service;

use App\Enum\PagePlacement;
use App\Repository\ClubSettingsRepository;
use App\Repository\PageRepository;
use App\Repository\PartnerRepository;
use App\Repository\SeasonRepository;
use App\Repository\SocialLinkRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class SiteContextBuilder
{
    public function __construct(
        private readonly SeasonRepository $seasonRepository,
        private readonly PageRepository $pageRepository,
        private readonly SocialLinkRepository $socialLinkRepository,
        private readonly PartnerRepository $partnerRepository,
        private readonly ClubSettingsRepository $clubSettingsRepository,
        private readonly Security $security,
        #[Autowire(service: 'cache.app')]
        private readonly CacheInterface $cache,
        #[Autowire('%app.club_name%')]
        private readonly string $clubName,
        #[Autowire('%app.contact_public_email%')]
        private readonly string $contactPublicEmail,
        #[Autowire('%app.contact_phone%')]
        private readonly string $contactPhone,
        #[Autowire('%app.contact_address%')]
        private readonly string $contactAddress,
        #[Autowire('%app.contact_map_url%')]
        private readonly string $contactMapUrl,
    ) {
    }

    public function build(): array
    {
        $user = $this->security->getUser();
        $shared = $this->cache->get('site_context.shared', function (ItemInterface $item): array {
            $item->expiresAfter(300);
            $settings = $this->clubSettingsRepository->getSettings();

            return [
                'name' => $settings?->getClubName() ?: $this->clubName,
                'current_season' => $this->seasonRepository->findCurrentSeason(),
                'archives' => $this->seasonRepository->findArchives(),
                'header_pages' => $this->pageRepository->findForPlacement(PagePlacement::Header),
                'footer_pages' => $this->pageRepository->findForPlacement(PagePlacement::Footer),
                'social_links' => $this->socialLinkRepository->findVisibleOrdered(),
                'contact' => [
                    'email' => $settings?->getPublicEmail() ?: $this->contactPublicEmail,
                    'phone' => $settings?->getPhone() ?: $this->contactPhone,
                    'address' => $settings?->getAddress() ?: $this->contactAddress,
                    'map_url' => $settings?->getMapUrl() ?: $this->contactMapUrl,
                ],
            ];
        });

        return [
            'site' => [
                ...$shared,
                'partners' => $this->partnerRepository->findVisibleOrdered(),
                'account' => [
                    'is_authenticated' => null !== $user,
                    'user' => $user,
                    'dashboard_path' => null !== $user ? '/compte' : null,
                ],
            ],
        ];
    }
}
