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
use App\Form\Model\PartnerRequestData;
use App\Form\Model\TrialRequestData;
use App\Form\Model\VolunteerRequestData;
use App\Form\PartnerRequestFormType;
use App\Form\TrialRequestFormType;
use App\Form\VolunteerRequestFormType;
use App\Repository\ArticleRepository;
use App\Repository\HomeSectionRepository;
use App\Repository\MatchGameRepository;
use App\Repository\PageRepository;
use App\Repository\PartnerRepository;
use App\Repository\PlayerRepository;
use App\Repository\RankingEntryRepository;
use App\Repository\SeasonRepository;
use App\Service\MatchArticleResolver;
use App\Service\SiteContextBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Attribute\IsGranted;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class SiteController extends AbstractController
{
    public function __construct(
        private readonly SiteContextBuilder $siteContextBuilder,
        private readonly MatchArticleResolver $matchArticleResolver,
    )
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
        $homeSections = $this->resolveHomeSections($homeSectionRepository);
        $featuredHomeSection = $homeSectionRepository->findConfiguredByKey(HomeSection::KEY_FEATURED_ARTICLE);
        $latestArticle = $this->resolveHomepageFeaturedArticle($featuredHomeSection, $articleRepository);
        $upcomingMatches = $matchRepository->findUpcomingMatches();
        $nextMatchDisplayLimit = 4;
        foreach ($homeSections as $section) {
            if (($section['key'] ?? null) === HomeSection::KEY_NEXT_MATCH) {
                $nextMatchDisplayLimit = max(1, (int) ($section['upcomingMatchesLimit'] ?? 4));
                break;
            }
        }
        $lastMatch = $matchRepository->findLastMatch();
        $candidateArticles = $articleRepository->findLatestPublished(50);
        $recentArticles = $this->resolveHomepageSecondaryArticles($featuredHomeSection, $articleRepository, $latestArticle);
        $discoverPage = $pageRepository->findPublishedBySlug('decouvrir-le-cecifoot');

        return $this->render('site/home.html.twig', [
            ...$this->siteContextBuilder->build(),
            'discoverPage' => $discoverPage,
            'latestArticle' => $latestArticle,
            'homeSections' => $homeSections,
            'nextMatch' => $upcomingMatches[0] ?? null,
            'otherUpcomingMatches' => array_slice($upcomingMatches, 1, max(0, $nextMatchDisplayLimit - 1)),
            'recentArticles' => $recentArticles,
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
            'previousArticle' => $articleRepository->findPreviousPublished($article),
            'nextArticle' => $articleRepository->findNextPublished($article),
            'preview_mode' => $this->canPreview($request),
        ]);
    }

    #[Route('/actualites', name: 'site_articles', methods: ['GET'])]
    public function articles(ArticleRepository $articleRepository): Response
    {
        $articles = $articleRepository->findLatestPublished(18);

        return $this->render('site/articles.html.twig', [
            ...$this->siteContextBuilder->build(),
            'featuredArticle' => $articles[0] ?? null,
            'articles' => array_slice($articles, 1),
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
                    'Nouveau message de contact reÃ§u depuis le site.',
                    'Nom : '.trim((string) $data->name),
                    'E-mail : '.trim((string) $data->email),
                    'Objet : '.($subject !== '' ? $subject : 'Sans objet'),
                    'Message :',
                    trim((string) $data->message),
                ]));

            $mailer->send($email);
            $this->addFlash('success', 'Votre message a bien Ã©tÃ© envoyÃ© au club.');

            return $this->redirectToRoute('site_contact');
        }

        return $this->render('site/contact.html.twig', [
            ...$this->siteContextBuilder->build(),
            'contactForm' => $form->createView(),
        ]);
    }

    #[Route('/partenaires', name: 'site_partners_static', methods: ['GET'])]
    public function partnersStatic(PageRepository $pageRepository, PartnerRepository $partnerRepository, Request $request): Response
    {
        return $this->renderManagedPage(Page::SYSTEM_KEY_PARTNERS, $pageRepository, $request, [
            'partners' => $partnerRepository->findVisibleOrdered(),
        ]);
    }

    #[Route('/rejoindre-le-club', name: 'site_join', methods: ['GET'])]
    public function join(PageRepository $pageRepository, Request $request): Response
    {
        return $this->renderManagedPage(Page::SYSTEM_KEY_JOIN, $pageRepository, $request);
    }

    #[Route('/seance-decouverte', name: 'site_trial_request', methods: ['GET', 'POST'])]
    public function trialRequest(Request $request, MailerInterface $mailer): Response
    {
        $data = new TrialRequestData();
        $form = $this->createForm(TrialRequestFormType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = (new Email())
                ->from((string) $this->getParameter('app.contact_from_email'))
                ->to((string) $this->getParameter('app.contact_to_email'))
                ->replyTo((string) $data->email)
                ->subject('[SÃ©ance dÃ©couverte] '.trim((string) $data->name))
                ->text(implode(PHP_EOL.PHP_EOL, [
                    'Nouvelle demande de sÃ©ance dÃ©couverte.',
                    'Nom : '.trim((string) $data->name),
                    'E-mail : '.trim((string) $data->email),
                    'TÃ©lÃ©phone : '.trim((string) ($data->phone ?: 'Non renseignÃ©')),
                    'Profil : '.trim((string) $data->profile),
                    'DisponibilitÃ©s : '.trim((string) ($data->availability ?: 'Non renseignÃ©es')),
                    'Message :',
                    trim((string) ($data->message ?: 'Aucun message complÃ©mentaire.')),
                ]));

            $mailer->send($email);
            $this->addFlash('success', 'Votre demande a bien Ã©tÃ© envoyÃ©e. Le club reviendra vers vous rapidement.');

            return $this->redirectToRoute('site_trial_request');
        }

        return $this->render('site/trial_request.html.twig', [
            ...$this->siteContextBuilder->build(),
            'trialRequestForm' => $form->createView(),
        ]);
    }

    #[Route('/benevolat', name: 'site_volunteer', methods: ['GET', 'POST'])]
    public function volunteer(Request $request, MailerInterface $mailer): Response
    {
        $data = new VolunteerRequestData();
        $form = $this->createForm(VolunteerRequestFormType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = (new Email())
                ->from((string) $this->getParameter('app.contact_from_email'))
                ->to((string) $this->getParameter('app.contact_to_email'))
                ->replyTo((string) $data->email)
                ->subject('[BÃ©nÃ©volat] '.trim((string) $data->name))
                ->text(implode(PHP_EOL.PHP_EOL, [
                    'Nouvelle proposition de bÃ©nÃ©volat.',
                    'Nom : '.trim((string) $data->name),
                    'E-mail : '.trim((string) $data->email),
                    'TÃ©lÃ©phone : '.trim((string) ($data->phone ?: 'Non renseignÃ©')),
                    'DisponibilitÃ©s : '.trim((string) ($data->availability ?: 'Non renseignÃ©es')),
                    'CompÃ©tences : '.trim((string) ($data->skills ?: 'Non renseignÃ©es')),
                    'Message :',
                    trim((string) $data->message),
                ]));

            $mailer->send($email);
            $this->addFlash('success', 'Votre proposition de bÃ©nÃ©volat a bien Ã©tÃ© envoyÃ©e.');

            return $this->redirectToRoute('site_volunteer');
        }

        return $this->render('site/volunteer.html.twig', [
            ...$this->siteContextBuilder->build(),
            'volunteerForm' => $form->createView(),
        ]);
    }

    #[Route('/devenir-partenaire', name: 'site_partner_request', methods: ['GET', 'POST'])]
    public function partnerRequest(Request $request, MailerInterface $mailer): Response
    {
        $data = new PartnerRequestData();
        $form = $this->createForm(PartnerRequestFormType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = (new Email())
                ->from((string) $this->getParameter('app.contact_from_email'))
                ->to((string) $this->getParameter('app.contact_to_email'))
                ->replyTo((string) $data->email)
                ->subject('[Partenariat] '.trim((string) $data->organization))
                ->text(implode(PHP_EOL.PHP_EOL, [
                    'Nouvelle demande de partenariat.',
                    'Structure : '.trim((string) $data->organization),
                    'Contact : '.trim((string) $data->contactName),
                    'E-mail : '.trim((string) $data->email),
                    'TÃ©lÃ©phone : '.trim((string) ($data->phone ?: 'Non renseignÃ©')),
                    'Type de soutien : '.trim((string) $data->supportType),
                    'Site web : '.trim((string) ($data->website ?: 'Non renseignÃ©')),
                    'Message :',
                    trim((string) $data->message),
                ]));

            $mailer->send($email);
            $this->addFlash('success', 'Votre demande de partenariat a bien Ã©tÃ© envoyÃ©e.');

            return $this->redirectToRoute('site_partner_request');
        }

        return $this->render('site/partner_request.html.twig', [
            ...$this->siteContextBuilder->build(),
            'partnerRequestForm' => $form->createView(),
        ]);
    }

    #[Route('/faq', name: 'site_faq', methods: ['GET'])]
    public function faq(PageRepository $pageRepository, Request $request): Response
    {
        return $this->renderManagedPage(Page::SYSTEM_KEY_FAQ, $pageRepository, $request);
    }

    #[Route('/entrainements', name: 'site_training', methods: ['GET'])]
    public function training(PageRepository $pageRepository, Request $request): Response
    {
        return $this->renderManagedPage(Page::SYSTEM_KEY_TRAINING, $pageRepository, $request);
    }

    #[Route('/acces', name: 'site_access', methods: ['GET'])]
    public function access(PageRepository $pageRepository, Request $request): Response
    {
        return $this->renderManagedPage(Page::SYSTEM_KEY_ACCESS, $pageRepository, $request);
    }

    #[Route('/encadrement', name: 'site_staff', methods: ['GET'])]
    public function staff(PageRepository $pageRepository, Request $request): Response
    {
        return $this->renderManagedPage(Page::SYSTEM_KEY_STAFF, $pageRepository, $request);
    }

    #[Route('/cgu', name: 'site_terms', methods: ['GET'])]
    public function terms(PageRepository $pageRepository, Request $request): Response
    {
        return $this->renderManagedPage(Page::SYSTEM_KEY_TERMS, $pageRepository, $request);
    }

    #[Route('/politique-de-confidentialite', name: 'site_privacy', methods: ['GET'])]
    public function privacy(PageRepository $pageRepository, Request $request): Response
    {
        return $this->renderManagedPage(Page::SYSTEM_KEY_PRIVACY, $pageRepository, $request);
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

        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $data = new AccountProfileData();
        $data->fullName = $user->getFullName();
        $data->email = $user->getEmail();

        $form = $this->createForm(AccountProfileFormType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => mb_strtolower((string) $data->email)]);

            if ($existingUser instanceof User && $existingUser->getId() !== $user->getId()) {
                $form->get('email')->addError(new FormError('Cette adresse e-mail est dÃ©jÃ  utilisÃ©e par un autre compte.'));
            } else {
                $user
                    ->setFullName((string) $data->fullName)
                    ->setEmail((string) $data->email);

                if (null !== $data->newPassword && '' !== $data->newPassword) {
                    $user->setPassword($passwordHasher->hashPassword($user, $data->newPassword));
                }

                $entityManager->flush();
                $this->addFlash('success', 'Ton compte a bien Ã©tÃ© mis Ã  jour.');

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
            $article = $this->matchArticleResolver->resolve($match, $articles);
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
        return $this->matchArticleResolver->resolve($match, $articles);
    }

    private function resolveHomepageFeaturedArticle(?HomeSection $featuredSection, ArticleRepository $articleRepository): ?Article
    {
        $featuredArticle = $featuredSection?->getFeaturedArticle();
        if ($featuredArticle instanceof Article && $featuredArticle->isVisibleOnSite()) {
            return $featuredArticle;
        }

        return $articleRepository->findLatestHomepageArticle();
    }

    /**
     * @return list<Article>
     */
    private function resolveHomepageSecondaryArticles(?HomeSection $featuredSection, ArticleRepository $articleRepository, ?Article $featuredArticle): array
    {
        $articles = [];
        $excludeIds = [];

        if ($featuredArticle instanceof Article && null !== $featuredArticle->getId()) {
            $excludeIds[] = $featuredArticle->getId();
        }

        foreach ($featuredSection?->getManualSecondaryArticles() ?? [] as $article) {
            if (!$article->isVisibleOnSite()) {
                continue;
            }

            if ($featuredArticle instanceof Article && $article->getId() === $featuredArticle->getId()) {
                continue;
            }

            $key = (string) ($article->getId() ?? spl_object_id($article));
            $articles[$key] = $article;

            if (null !== $article->getId()) {
                $excludeIds[] = $article->getId();
            }

            if (count($articles) >= 3) {
                return array_values($articles);
            }
        }

        $autoArticles = $articleRepository->findHomepageSecondaryArticles(3 - count($articles), $excludeIds);
        foreach ($autoArticles as $article) {
            $key = (string) ($article->getId() ?? spl_object_id($article));
            $articles[$key] = $article;
        }

        return array_values($articles);
    }

    /**
     * @return array<int, array{
     *     key: string,
     *     title: string,
     *     content: ?string,
     *     image: ?string,
     *     titleTag: string,
     *     textAlignment: string,
     *     layoutWidth: string,
     *     showImage: bool,
     *     imagePosition: string,
     *     appearance: string,
     *     accentTone: string,
     *     showTag: bool,
     *     showMeta: bool,
     *     showExcerpt: bool,
     *     showScore: bool,
     *     upcomingMatchesLimit: int,
     *     displayOrder: int
     * }>
     */
    private function resolveHomeSections(HomeSectionRepository $homeSectionRepository): array
    {
        $resolved = [];
        foreach (HomeSection::defaultDefinitions() as $definition) {
            $resolved[$definition['sectionKey']] = [
                'key' => $definition['sectionKey'],
                'title' => $definition['title'],
                'content' => $definition['content'],
                'image' => $definition['image'] ?? null,
                'titleTag' => $definition['titleTag'] ?? 'h2',
                'textAlignment' => $definition['textAlignment'] ?? 'left',
                'layoutWidth' => $definition['layoutWidth'] ?? 'wide',
                'showImage' => $definition['showImage'] ?? true,
                'imagePosition' => $definition['imagePosition'] ?? 'start',
                'appearance' => $definition['appearance'] ?? 'default',
                'accentTone' => $definition['accentTone'] ?? 'green',
                'showTag' => $definition['showTag'] ?? true,
                'showMeta' => $definition['showMeta'] ?? true,
                'showExcerpt' => $definition['showExcerpt'] ?? true,
                'showScore' => $definition['showScore'] ?? true,
                'upcomingMatchesLimit' => $definition['upcomingMatchesLimit'] ?? 4,
                'displayOrder' => $definition['displayOrder'],
                'isEnabled' => $definition['isEnabled'],
            ];
        }

        foreach ($homeSectionRepository->findAllOrdered() as $section) {
            $key = $section->getSectionKey();
            if (null === $key) {
                continue;
            }

            $resolved[$key] = [
                'key' => $section->getSectionKey(),
                'title' => $section->getTitle() ?? '',
                'content' => $section->getContent(),
                'image' => $section->getImage(),
                'titleTag' => $section->getTitleTag(),
                'textAlignment' => $section->getTextAlignment(),
                'layoutWidth' => $section->getLayoutWidth(),
                'showImage' => $section->isShowImage(),
                'imagePosition' => $section->getImagePosition(),
                'appearance' => $section->getAppearance(),
                'accentTone' => $section->getAccentTone(),
                'showTag' => $section->isShowTag(),
                'showMeta' => $section->isShowMeta(),
                'showExcerpt' => $section->isShowExcerpt(),
                'showScore' => $section->isShowScore(),
                'upcomingMatchesLimit' => $section->getUpcomingMatchesLimit(),
                'displayOrder' => $section->getDisplayOrder(),
                'isEnabled' => $section->isEnabled(),
            ];
        }

        $resolved = array_filter(
            $resolved,
            static fn (array $section): bool => (bool) $section['isEnabled']
        );

        uasort(
            $resolved,
            static fn (array $left, array $right): int => [$left['displayOrder'], $left['key']] <=> [$right['displayOrder'], $right['key']]
        );

        return array_values(array_map(
            static fn (array $section): array => [
                'key' => $section['key'],
                'title' => $section['title'],
                'content' => $section['content'],
                'image' => $section['image'],
                'titleTag' => $section['titleTag'],
                'textAlignment' => $section['textAlignment'],
                'layoutWidth' => $section['layoutWidth'],
                'showImage' => $section['showImage'],
                'imagePosition' => $section['imagePosition'],
                'appearance' => $section['appearance'],
                'accentTone' => $section['accentTone'],
                'showTag' => $section['showTag'],
                'showMeta' => $section['showMeta'],
                'showExcerpt' => $section['showExcerpt'],
                'showScore' => $section['showScore'],
                'upcomingMatchesLimit' => $section['upcomingMatchesLimit'],
                'displayOrder' => $section['displayOrder'],
            ],
            $resolved
        ));
    }

    private function canPreview(Request $request): bool
    {
        return $request->query->getBoolean('preview') && $this->isGranted('ROLE_ADMIN');
    }

    private function renderManagedPage(string $systemKey, PageRepository $pageRepository, Request $request, array $context = []): Response
    {
        $page = $this->canPreview($request)
            ? $pageRepository->findAnyBySystemKey($systemKey)
            : $pageRepository->findPublishedBySystemKey($systemKey);

        if (!$page instanceof Page) {
            throw new NotFoundHttpException('Page introuvable.');
        }

        return $this->render('site/page.html.twig', [
            ...$this->siteContextBuilder->build(),
            ...$context,
            'page' => $page,
            'preview_mode' => $this->canPreview($request),
        ]);
    }
}
