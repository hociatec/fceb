<?php

namespace App\Controller\Api;

use App\Enum\NavigationItemLocation;
use App\Enum\NavigationItemType;
use App\Repository\NavigationItemRepository;
use App\Repository\SeasonRepository;
use App\Repository\SocialLinkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/navigation', name: 'api_navigation_', format: 'json')]
class NavigationController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        SeasonRepository $seasonRepository,
        NavigationItemRepository $navigationItemRepository,
        SocialLinkRepository $socialLinkRepository,
        UrlGeneratorInterface $urlGenerator,
    ): JsonResponse {
        $currentSeason = $seasonRepository->findCurrentSeason();
        $archives = $seasonRepository->findArchives();

        return $this->json([
            'header' => [
                [
                    'label' => 'Saison en cours',
                    'slug' => $currentSeason?->getSlug(),
                    'href' => $currentSeason ? '/api/seasons/'.$currentSeason->getSlug() : null,
                ],
                [
                    'label' => 'Archives',
                    'href' => '/api/seasons/archives',
                    'children' => array_map(static fn ($season) => [
                        'label' => $season->getName(),
                        'href' => '/api/seasons/'.$season->getSlug(),
                    ], $archives),
                ],
                ...array_map(static function ($item) use ($urlGenerator) {
                    $href = match ($item->getType()) {
                        NavigationItemType::Route => $item->getRouteName() ? $urlGenerator->generate($item->getRouteName()) : null,
                        NavigationItemType::Page => $item->getPage()?->getSlug() ? $urlGenerator->generate('site_page', ['slug' => $item->getPage()?->getSlug()]) : null,
                        NavigationItemType::Url => $item->getExternalUrl(),
                    };

                    return [
                        'label' => $item->getLabel(),
                        'href' => $href,
                    ];
                }, $navigationItemRepository->findEnabledByLocationOrdered(NavigationItemLocation::Header)),
            ],
            'social' => array_map(static fn ($link) => [
                'label' => $link->getLabel(),
                'url' => $link->getUrl(),
                'icon' => $link->getIcon(),
            ], $socialLinkRepository->findVisibleOrdered()),
            'account' => [
                'authenticated' => null !== $this->getUser(),
                'links' => [
                    ['label' => 'Connexion', 'href' => '/login'],
                    ['label' => 'Inscription', 'href' => '/register'],
                    ['label' => 'Déconnexion', 'href' => '/logout'],
                ],
            ],
            'footer' => array_map(static function ($item) use ($urlGenerator) {
                $href = match ($item->getType()) {
                    NavigationItemType::Route => $item->getRouteName() ? $urlGenerator->generate($item->getRouteName()) : null,
                    NavigationItemType::Page => $item->getPage()?->getSlug() ? $urlGenerator->generate('site_page', ['slug' => $item->getPage()?->getSlug()]) : null,
                    NavigationItemType::Url => $item->getExternalUrl(),
                };

                return [
                    'label' => $item->getLabel(),
                    'href' => $href,
                ];
            }, $navigationItemRepository->findEnabledByLocationOrdered(NavigationItemLocation::Footer)),
        ]);
    }
}
