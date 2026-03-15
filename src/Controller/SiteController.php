<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\HomeSection;
use App\Entity\MatchGame;
use App\Entity\Player;
use App\Entity\User;
use App\Form\AccountProfileFormType;
use App\Form\ContactFormType;
use App\Form\Model\AccountProfileData;
use App\Form\Model\ContactData;
use App\Repository\ArticleRepository;
use App\Repository\HomeSectionRepository;
use App\Repository\MatchGameRepository;
use App\Repository\PageRepository;
use App\Repository\PartnerRepository;
use App\Repository\PlayerRepository;
use App\Repository\RankingEntryRepository;
use App\Repository\SeasonRepository;
use App\Service\SiteContextBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class SiteController extends AbstractController
{
    public function __construct(private readonly SiteContextBuilder $siteContextBuilder)
    {
    }

    #[Route('/', name: 'site_home', methods: ['GET'])]
    public function home(
        ArticleRepository $articleRepository,
        HomeSectionRepository $homeSectionRepository,
        MatchGameRepository $matchRepository,
        PageRepository $pageRepository,
        PartnerRepository $partnerRepository
    ): Response {
        $latestArticle = $articleRepository->findLatestHomepageArticle();
        $upcomingMatches = $matchRepository->findUpcomingMatches();
        $lastMatch = $matchRepository->findLastMatch();
        $candidateArticles = $articleRepository->findLatestPublished(50);
        $discoverPage = $pageRepository->findPublishedBySlug('decouvrir-le-cecifoot');

        return $this->render('site/home.html.twig', [
            ...$this->siteContextBuilder->build(),
            'discoverPage' => $discoverPage,
            'discoverPageExcerpt' => $this->buildPageExcerpt($discoverPage?->getContent()),
            'latestArticle' => $latestArticle,
            'homeSections' => $this->resolveHomeSections($homeSectionRepository),
            'upcomingMatches' => $upcomingMatches,
            'lastMatch' => $lastMatch,
            'lastMatchArticle' => $this->findMatchingArticleForMatch($lastMatch, $candidateArticles),
            'partners' => $partnerRepository->findVisibleOrdered(),
        ]);
    }

    #[Route('/saison-en-cours', name: 'site_current_season', methods: ['GET'])]
    public function currentSeason(
        SeasonRepository $seasonRepository,
        ArticleRepository $articleRepository,
        MatchGameRepository $matchRepository
    ): Response {
        $season = $seasonRepository->findCurrentSeason();
        if (!$season) {
            throw new NotFoundHttpException('Aucune saison en cours.');
        }

        $articles = $articleRepository->findPublishedBySeason($season);
        $matches = $matchRepository->findBySeasonOrdered($season);

        return $this->render('site/season.html.twig', [
            ...$this->siteContextBuilder->build(),
            'season' => $season,
            'articles' => $articles,
            'matches' => $matches,
            'matchArticles' => $this->buildMatchArticleMap($matches, $articles),
            'isArchive' => false,
        ]);
    }

    #[Route('/classement', name: 'site_ranking', methods: ['GET'])]
    public function ranking(SeasonRepository $seasonRepository, RankingEntryRepository $rankingEntryRepository): Response
    {
        $season = $seasonRepository->findCurrentSeason() ?? $rankingEntryRepository->findLatestSeasonWithEntries();
        $entries = $season ? $rankingEntryRepository->findBySeasonOrdered($season) : [];

        return $this->render('site/ranking.html.twig', [
            ...$this->siteContextBuilder->build(),
            'season' => $season,
            'entries' => $entries,
        ]);
    }

    #[Route('/effectif', name: 'site_players', methods: ['GET'])]
    public function players(PlayerRepository $playerRepository): Response
    {
        return $this->render('site/players.html.twig', [
            ...$this->siteContextBuilder->build(),
            'players' => $playerRepository->findPublishedOrdered(),
        ]);
    }

    #[Route('/effectif/{slug}', name: 'site_player_show', methods: ['GET'])]
    public function playerShow(string $slug, PlayerRepository $playerRepository, Request $request): Response
    {
        $player = $this->canPreview($request)
            ? $playerRepository->findAnyBySlug($slug)
            : $playerRepository->findPublishedBySlug($slug);

        if (!$player instanceof Player) {
            throw new NotFoundHttpException('Joueur introuvable.');
        }

        return $this->render('site/player_show.html.twig', [
            ...$this->siteContextBuilder->build(),
            'player' => $player,
            'preview_mode' => $this->canPreview($request),
        ]);
    }

    #[Route('/calendrier', name: 'site_calendar', methods: ['GET'])]
    public function calendar(
        SeasonRepository $seasonRepository,
        MatchGameRepository $matchRepository,
        ArticleRepository $articleRepository
    ): Response {
        $season = $seasonRepository->findCurrentSeason() ?? $seasonRepository->findOneBy([], ['startDate' => 'DESC']);
        $upcomingMatches = $season ? $matchRepository->findUpcomingBySeason($season) : [];
        $pastMatches = $season ? $matchRepository->findPastBySeason($season) : [];
        $articles = $season ? $articleRepository->findPublishedBySeason($season) : [];
        $matches = [...$upcomingMatches, ...$pastMatches];

        return $this->render('site/calendar.html.twig', [
            ...$this->siteContextBuilder->build(),
            'season' => $season,
            'upcomingMatches' => $upcomingMatches,
            'pastMatches' => $pastMatches,
            'matchArticles' => $this->buildMatchArticleMap($matches, $articles),
        ]);
    }

    #[Route('/archives', name: 'site_archives', methods: ['GET'])]
    public function archives(SeasonRepository $seasonRepository): Response
    {
        return $this->render('site/archives.html.twig', [
            ...$this->siteContextBuilder->build(),
            'seasons' => $seasonRepository->findArchives(),
        ]);
    }

    #[Route('/archives/{slug}', name: 'site_archive_show', methods: ['GET'])]
    public function archiveShow(
        string $slug,
        SeasonRepository $seasonRepository,
        ArticleRepository $articleRepository,
        MatchGameRepository $matchRepository
    ): Response {
        $season = $seasonRepository->findOneBy(['slug' => $slug]);
        if (!$season) {
            throw new NotFoundHttpException('Saison introuvable.');
        }

        $articles = $articleRepository->findPublishedBySeason($season);
        $matches = $matchRepository->findBySeasonOrdered($season);

        return $this->render('site/season.html.twig', [
            ...$this->siteContextBuilder->build(),
            'season' => $season,
            'articles' => $articles,
            'matches' => $matches,
            'matchArticles' => $this->buildMatchArticleMap($matches, $articles),
            'isArchive' => !$season->isCurrent(),
        ]);
    }

    #[Route('/pages/{slug}', name: 'site_page', methods: ['GET'])]
    public function page(string $slug, PageRepository $pageRepository, Request $request): Response
    {
        $page = $this->canPreview($request)
            ? $pageRepository->findAnyBySlug($slug)
            : $pageRepository->findPublishedBySlug($slug);

        if (!$page) {
            throw new NotFoundHttpException('Page introuvable.');
        }

        return $this->render('site/page.html.twig', [
            ...$this->siteContextBuilder->build(),
            'page' => $page,
            'preview_mode' => $this->canPreview($request),
        ]);
    }

    #[Route('/actualites/{slug}', name: 'site_article', methods: ['GET'])]
    public function article(string $slug, ArticleRepository $articleRepository, Request $request): Response
    {
        $article = $this->canPreview($request)
            ? $articleRepository->findAnyBySlug($slug)
            : $articleRepository->findPublishedBySlug($slug);

        if (!$article) {
            throw new NotFoundHttpException('Article introuvable.');
        }

        return $this->render('site/article.html.twig', [
            ...$this->siteContextBuilder->build(),
            'article' => $article,
            'preview_mode' => $this->canPreview($request),
        ]);
    }

    #[Route('/contact', name: 'site_contact', methods: ['GET', 'POST'])]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $data = new ContactData();
        $form = $this->createForm(ContactFormType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $subject = trim((string) $data->subject);

            $email = (new Email())
                ->from((string) $this->getParameter('app.contact_from_email'))
                ->to((string) $this->getParameter('app.contact_to_email'))
                ->replyTo((string) $data->email)
                ->subject('[Contact site] '.($subject !== '' ? $subject : 'Sans objet'))
                ->text(implode(PHP_EOL.PHP_EOL, [
                    'Nouveau message de contact reçu depuis le site.',
                    'Nom : '.trim((string) $data->name),
                    'E-mail : '.trim((string) $data->email),
                    'Objet : '.($subject !== '' ? $subject : 'Sans objet'),
                    'Message :',
                    trim((string) $data->message),
                ]));

            $mailer->send($email);
            $this->addFlash('success', 'Votre message a bien été envoyé au club.');

            return $this->redirectToRoute('site_contact');
        }

        return $this->render('site/contact.html.twig', [
            ...$this->siteContextBuilder->build(),
            'contactForm' => $form->createView(),
        ]);
    }

    #[Route('/partenaires', name: 'site_partners_static', methods: ['GET'])]
    public function partnersStatic(PartnerRepository $partnerRepository): Response
    {
        return $this->render('site/static_page.html.twig', [
            ...$this->siteContextBuilder->build(),
            'eyebrow' => 'Partenaires',
            'title' => 'Partenaires et soutiens',
            'content' => [
                "Le club s'inscrit dans l'écosystème sportif local de La Bassée et dans le réseau handisport.",
                "Cette page présente les structures qui accompagnent le développement du cécifoot, la vie associative et l'accueil des joueurs.",
                'Tu pourras ensuite compléter cette rubrique avec les soutiens institutionnels, les partenaires privés et les liens utiles du club.',
            ],
            'partners' => $partnerRepository->findVisibleOrdered(),
        ]);
    }

    #[Route('/cgu', name: 'site_terms', methods: ['GET'])]
    public function terms(): Response
    {
        return $this->render('site/static_page.html.twig', [
            ...$this->siteContextBuilder->build(),
            'eyebrow' => 'Informations légales',
            'title' => "Conditions générales d'utilisation",
            'content' => [
                "Le site a pour objet de présenter l'activité du club, ses actualités, ses saisons et ses informations pratiques.",
                "L'utilisateur s'engage à utiliser les formulaires et les espaces de connexion de manière loyale, sans nuire au fonctionnement du site.",
                "Le club se réserve le droit de faire évoluer les contenus, les accès et l'organisation du site à tout moment.",
            ],
        ]);
    }

    #[Route('/politique-de-confidentialite', name: 'site_privacy', methods: ['GET'])]
    public function privacy(): Response
    {
        return $this->render('site/static_page.html.twig', [
            ...$this->siteContextBuilder->build(),
            'eyebrow' => 'Données personnelles',
            'title' => 'Politique de confidentialité',
            'content' => [
                'Les données envoyées via le formulaire de contact sont utilisées uniquement pour répondre aux demandes adressées au club.',
                'Le site limite la collecte aux informations nécessaires au traitement de la demande : nom, e-mail, objet et message.',
                'Toute demande relative aux données personnelles peut être adressée au club via la page de contact.',
            ],
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/compte', name: 'site_account', methods: ['GET', 'POST'])]
    public function account(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        $data = new AccountProfileData();
        $data->fullName = $user->getFullName();
        $data->email = $user->getEmail();

        $form = $this->createForm(AccountProfileFormType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => mb_strtolower((string) $data->email)]);

            if ($existingUser instanceof User && $existingUser->getId() !== $user->getId()) {
                $form->get('email')->addError(new \Symfony\Component\Form\FormError('Cette adresse e-mail est déjà utilisée par un autre compte.'));
            } else {
                $user
                    ->setFullName((string) $data->fullName)
                    ->setEmail((string) $data->email);

                if (null !== $data->newPassword && '' !== $data->newPassword) {
                    $user->setPassword($passwordHasher->hashPassword($user, $data->newPassword));
                }

                $entityManager->flush();
                $this->addFlash('success', 'Ton compte a bien été mis à jour.');

                return $this->redirectToRoute('site_account');
            }
        }

        return $this->render('site/account.html.twig', [
            ...$this->siteContextBuilder->build(),
            'member' => $user,
            'accountForm' => $form->createView(),
        ]);
    }

    /**
     * @param MatchGame[] $matches
     * @param Article[] $articles
     *
     * @return array<int, Article>
     */
    private function buildMatchArticleMap(array $matches, array $articles): array
    {
        $map = [];

        foreach ($matches as $match) {
            $article = $this->findMatchingArticleForMatch($match, $articles);
            if ($article) {
                $map[$match->getId()] = $article;
            }
        }

        return $map;
    }

    /**
     * @param Article[] $articles
     */
    private function findMatchingArticleForMatch(?MatchGame $match, array $articles): ?Article
    {
        if (!$match instanceof MatchGame) {
            return null;
        }

        $opponent = mb_strtolower($match->getOpponent() ?? '');
        $matchDay = $match->getMatchDate()?->format('Y-m-d');

        foreach ($articles as $article) {
            if (!$article->getPublishedAt() || $article->getPublishedAt()->format('Y-m-d') !== $matchDay) {
                continue;
            }

            $title = mb_strtolower($article->getTitle() ?? '');
            if ('' !== $opponent && str_contains($title, $opponent)) {
                return $article;
            }
        }

        return null;
    }

    /**
     * @return array<int, array{key: string, title: string, subtitle: ?string}>
     */
    private function resolveHomeSections(HomeSectionRepository $homeSectionRepository): array
    {
        $sections = $homeSectionRepository->findEnabledOrdered();
        if ([] === $sections) {
            return array_map(
                static fn (array $definition): array => [
                    'key' => $definition['sectionKey'],
                    'title' => $definition['title'],
                    'subtitle' => $definition['subtitle'],
                ],
                HomeSection::defaultDefinitions()
            );
        }

        return array_map(
            static fn (HomeSection $section): array => [
                'key' => $section->getSectionKey(),
                'title' => $section->getTitle() ?? '',
                'subtitle' => $section->getSubtitle(),
            ],
            $sections
        );
    }

    private function buildPageExcerpt(?string $content): ?string
    {
        if (null === $content) {
            return null;
        }

        $normalized = preg_replace('/\[(quote|separator|cta)([^\]]*)\]/i', ' ', $content);
        $normalized = preg_replace('/\[\/quote\]/i', ' ', $normalized ?? '');
        $plainText = trim(html_entity_decode(strip_tags((string) $normalized)));
        $plainText = preg_replace('/\s+/', ' ', $plainText ?? '');

        if ('' === $plainText) {
            return null;
        }

        return mb_strimwidth($plainText, 0, 220, '...');
    }

    private function canPreview(Request $request): bool
    {
        return $request->query->getBoolean('preview') && $this->isGranted('ROLE_ADMIN');
    }
}
