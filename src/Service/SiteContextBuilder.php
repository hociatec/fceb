<?php

namespace App\Service;

use App\Enum\PagePlacement;
use App\Repository\ClubSettingsRepository;
use App\Repository\PageRepository;
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
                'homepage' => [
                    'intro_title' => $settings?->getHomeIntroTitle() ?: $this->clubName,
                    'intro_subtitle' => $settings?->getHomeIntroSubtitle() ?: "Un club qui assume à la fois l'exigence sportive, l'accessibilité et l'ancrage local.",
                    'intro_lead' => $settings?->getHomeIntroLead() ?: "À La Bassée, le cécifoot se construit autour d'un cadre clair : engagement, solidarité, écoute, progression et goût du jeu. Le club défend une pratique accessible, sérieuse et vivante, pensée pour accueillir durablement les joueurs, les proches, les bénévoles et les partenaires.",
                    'intro_media_note' => $settings?->getHomeIntroMediaNote() ?: 'Chaque visuel publié sur le site a vocation à montrer le terrain, les temps forts du collectif et la réalité de la vie du club.',
                    'featured_title' => $settings?->getHomeFeaturedTitle() ?: 'Actualité à la une',
                    'featured_subtitle' => $settings?->getHomeFeaturedSubtitle() ?: 'Le contenu principal à retenir en ce moment, avec un angle éditorial plus net.',
                    'upcoming_title' => $settings?->getHomeUpcomingTitle() ?: 'Prochain match',
                    'upcoming_subtitle' => $settings?->getHomeUpcomingSubtitle() ?: "Le prochain rendez-vous sportif du club, avec les repères utiles en un coup d'œil.",
                    'last_result_title' => $settings?->getHomeLastResultTitle() ?: 'Dernier résultat',
                    'last_result_subtitle' => $settings?->getHomeLastResultSubtitle() ?: 'Le dernier score enregistré, avec son compte-rendu quand il existe.',
                ],
            ];
        });

        return [
            'site' => [
                ...$shared,
                'account' => [
                    'is_authenticated' => null !== $user,
                    'user' => $user,
                    'dashboard_path' => null !== $user ? '/compte' : null,
                ],
            ],
        ];
    }
}
