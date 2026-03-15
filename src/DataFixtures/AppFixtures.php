<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\MatchGame;
use App\Entity\Page;
use App\Entity\Partner;
use App\Entity\Season;
use App\Entity\SocialLink;
use App\Entity\User;
use App\Enum\ArticlePlacement;
use App\Enum\MatchStatus;
use App\Enum\PagePlacement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = (new User())
            ->setFullName('Administrateur Cécifoot')
            ->setEmail('admin@cecifoot-labassee.local')
            ->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin1234!'));
        $manager->persist($admin);

        $currentSeason = (new Season())
            ->setName('Saison 2025-2026')
            ->setSlug('saison-2025-2026')
            ->setStartDate(new \DateTimeImmutable('2025-09-01'))
            ->setEndDate(new \DateTimeImmutable('2026-06-30'))
            ->setIsCurrent(true);
        $manager->persist($currentSeason);

        $archiveSeason = (new Season())
            ->setName('Saison 2024-2025')
            ->setSlug('saison-2024-2025')
            ->setStartDate(new \DateTimeImmutable('2024-09-01'))
            ->setEndDate(new \DateTimeImmutable('2025-06-30'))
            ->setIsCurrent(false);
        $manager->persist($archiveSeason);

        $manager->persist(
            (new Article())
                ->setTitle('La Bassée, nouveau participant de la poule Challenger')
                ->setSlug('la-bassee-nouveau-participant-poule-challenger')
                ->setExcerpt('Le FC Cécifoot 59 La Bassée figure parmi les nouveaux engagés de la poule Challenger 2025-2026.')
                ->setContent("D'après Lensois.com, La Bassée fait partie des nouveaux participants de la poule Challenger pour la saison 2025-2026, aux côtés notamment du FC Nantes. Cette entrée dans le championnat national confirme la structuration du projet cécifoot local et son ancrage dans le territoire.")
                ->setPublishedAt(new \DateTimeImmutable('2025-10-31 10:00:00'))
                ->setPlacement(ArticlePlacement::Homepage)
                ->setSeason($currentSeason)
                ->setAuthor($admin)
                ->setIsPublished(true)
        );

        $manager->persist(
            (new Article())
                ->setTitle('La Bassée accueillera une journée Challenger le 28 mars 2026')
                ->setSlug('la-bassee-accueillera-journee-challenger-28-mars-2026')
                ->setExcerpt('Le calendrier fédéral handisport annonce une journée B1 Challenger à La Bassée le 28 mars 2026.')
                ->setContent("Le calendrier 2026 de la Fédération française handisport annonce une journée de Championnat de France B1 Challenger à La Bassée le 28 mars 2026. La fiche 'Où pratiquer' de la FFH référence également cette date pour le club, avec un contact local dédié.")
                ->setPublishedAt(new \DateTimeImmutable('2026-03-01 09:00:00'))
                ->setPlacement(ArticlePlacement::CurrentSeason)
                ->setSeason($currentSeason)
                ->setAuthor($admin)
                ->setIsPublished(true)
        );

        $manager->persist(
            (new Article())
                ->setTitle('Retour sur les premières références publiques du club')
                ->setSlug('retour-premieres-references-publiques-du-club')
                ->setExcerpt('Le club apparaît dans les répertoires handisport et dans les résultats publiés de la saison.')
                ->setContent("Les répertoires publics de la FFH mentionnent le FC Cécifoot 59 La Bassée avec un contact spécifique, tandis que les résultats publiés par les structures voisines confirment sa présence dans la compétition nationale B1 Challenger.")
                ->setPublishedAt(new \DateTimeImmutable('2025-06-28 18:00:00'))
                ->setPlacement(ArticlePlacement::Archive)
                ->setSeason($archiveSeason)
                ->setAuthor($admin)
                ->setIsPublished(true)
        );

        $manager->persist(
            (new MatchGame())
                ->setSeason($currentSeason)
                ->setOpponent('Clermont Joker\'s')
                ->setCompetition('Championnat de France B1 Challenger')
                ->setLocation('La Bassée')
                ->setMatchDate(new \DateTimeImmutable('2026-03-28 11:00:00'))
                ->setSide('home')
                ->setStatus(MatchStatus::Scheduled)
        );

        $manager->persist(
            (new MatchGame())
                ->setSeason($currentSeason)
                ->setOpponent('RC Lens Cécifoot')
                ->setCompetition('Championnat de France B1 Challenger')
                ->setLocation('Lens')
                ->setMatchDate(new \DateTimeImmutable('2025-11-08 14:00:00'))
                ->setSide('away')
                ->setOurScore(0)
                ->setOpponentScore(1)
                ->setStatus(MatchStatus::Completed)
        );

        foreach ([
            ['Contact', 'contact', "Contact cécifoot référencé par la Fédération française handisport : martine.cecifoot59@gmail.com - 06 86 94 74 77.\nPoint d'ancrage local : Mairie de La Bassée, place du Général de Gaulle, 59480 La Bassée.", PagePlacement::Footer, 10],
            ['CGU', 'cgu', "Conditions générales d'utilisation du site public, de l'espace membre et de l'API du club.", PagePlacement::Footer, 20],
            ['Partenaires', 'partenaires', "Le club s'inscrit dans l'écosystème sportif local de La Bassée et dans le réseau handisport national.\nRetrouvez les partenaires et structures utiles depuis cette page.", PagePlacement::Footer, 30],
            ['Le club', 'le-club', "Le projet cécifoot de La Bassée s'inscrit dans le Football Club Espérance de La Bassée, référencé sur le portail associatif municipal avec une pratique au Stade Roland Joly.\nLe répertoire handisport recense également le FC Cécifoot 59 La Bassée avec un contact spécifique pour la discipline.", PagePlacement::Header, 10],
        ] as [$title, $slug, $content, $placement, $order]) {
            $manager->persist(
                (new Page())
                    ->setTitle($title)
                    ->setSlug($slug)
                    ->setContent($content)
                    ->setPlacement($placement)
                    ->setMenuOrder($order)
                    ->setIsPublished(true)
            );
        }

        foreach ([
            ['Facebook', 'https://facebook.com/cecifootlabassee', 'facebook', 10],
            ['Instagram', 'https://instagram.com/cecifootlabassee', 'instagram', 20],
        ] as [$label, $url, $icon, $order]) {
            $manager->persist(
                (new SocialLink())
                    ->setLabel($label)
                    ->setUrl($url)
                    ->setIcon($icon)
                    ->setDisplayOrder($order)
                    ->setIsVisible(true)
            );
        }

        $manager->persist(
            (new Partner())
                ->setName('Ville de La Bassée')
                ->setWebsiteUrl('https://www.ville-labassee.fr')
                ->setLogoUrl('https://placehold.co/300x140?text=Ville+de+La+Bassee')
                ->setDisplayOrder(10)
                ->setIsVisible(true)
        );

        $manager->persist(
            (new Partner())
                ->setName('Fédération Française Handisport')
                ->setWebsiteUrl('https://www.handisport.org')
                ->setLogoUrl('https://placehold.co/300x140?text=FFH')
                ->setDisplayOrder(20)
                ->setIsVisible(true)
        );

        $manager->flush();
    }
}
