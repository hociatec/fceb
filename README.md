# Cecifoot La Bassee API

API Symfony pour le site du club, avec administration EasyAdmin et base MySQL.

## Fonctionnel

- API JSON pour l'accueil, la navigation, les saisons, les pages, les articles et les partenaires
- administration pour gerer saisons, articles, matchs, pages, reseaux sociaux et partenaires
- connexion et inscription
- structure prevue pour la saison en cours et les archives

## Lancer le projet

```bash
docker compose up -d database mailer
composer install
php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction
php -S 127.0.0.1:8000 -t public
```

Tests (environnement `test`) : la suite utilise MySQL sur une base suffixee `_test`. En local, creez-la une fois avec `php bin/console doctrine:database:create --env=test --if-not-exists`, puis lancez `php bin/phpunit`.

Les migrations inserent aussi le contenu initial editable en base: compte admin de demo, saisons, actualites, matchs, pages, reseaux sociaux et partenaires.

## Base de donnees

- Configuration par defaut: MySQL
- URL locale par defaut: `mysql://app:!ChangeMe!@127.0.0.1:3306/fceb?serverVersion=8.0.32&charset=utf8mb4`
- URL de test par defaut: `mysql://app:!ChangeMe!@127.0.0.1:3306/fceb?serverVersion=8.0.32&charset=utf8mb4`, resolue vers `fceb_test`
- Le `docker compose` du projet publie MySQL sur `127.0.0.1:3306`, en coherence avec cette URL par defaut
- Un fichier `.env.local` ou une vraie variable d'environnement peut surcharger cette URL si vous utilisez une autre instance MySQL
- Un fichier `.env.test.local` peut surcharger la base de test si votre MySQL local n'utilise pas les identifiants/ports par defaut
- Pensez a definir `DATABASE_URL` sur le serveur de production avec vos vrais identifiants

## Mise en production MySQL

1. Creer une base MySQL distante, par exemple `fceb_prod`
2. Creer un utilisateur MySQL dedie avec tous les droits sur cette base
3. Copier les variables de `.env.prod.local.example` dans `.env.prod.local` sur le serveur
4. Importer le dump SQL genere localement dans la base distante
5. Installer le projet en production :

```bash
composer install --no-dev --optimize-autoloader
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod
```

Exemple d'import SQL sur le serveur distant :

```bash
mysql -u fceb_user -p fceb_prod < fceb-mysql.sql
```

Si votre MySQL de production est publie sur un autre port local, adaptez simplement `DATABASE_URL` dans `.env.prod.local`.

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
