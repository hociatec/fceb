# Cecifoot La Bassee API

API Symfony pour le site du club, avec administration EasyAdmin.

## Fonctionnel

- API JSON pour l'accueil, la navigation, les saisons, les pages, les articles et les partenaires
- administration pour gerer saisons, articles, matchs, pages, reseaux sociaux et partenaires
- connexion et inscription
- structure prevue pour la saison en cours et les archives

## Lancer le projet

```bash
composer install
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:fixtures:load --no-interaction
php -S 127.0.0.1:8000 -t public
```

## Acces demo

- Admin: `admin@cecifoot-labassee.local`
- Mot de passe: `Admin1234!`

## Endpoints utiles

- `GET /api/home`
- `GET /api/navigation`
- `GET /api/seasons/current`
- `GET /api/seasons/archives`
- `GET /api/seasons/{slug}`
- `GET /api/articles`
- `GET /api/pages/{slug}`
- `GET /api/partners`
- `POST /api/register`
