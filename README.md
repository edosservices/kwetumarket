# Twende Market

Twende Market est une marketplace qui relie clients, vendeurs, boutiques, livreurs et administrateurs. Cette première version pose la fondation : identité visuelle officielle, design system, authentification, rôles et permissions, et une API REST versionnée.

Le logo présent dans `public/brand/twende-market-logo.png` est l'identité officielle fournie par le propriétaire. Il est servi tel quel, via le composant `<x-brand-logo />` et la clé `config('twende.logo')`.

## Stack

- PHP 8.3
- Laravel 13
- MySQL / MariaDB
- Blade, Livewire, Alpine.js, Tailwind CSS
- Vite
- Laravel Fortify (authentification)
- Laravel Sanctum (jetons d'API)
- Spatie Laravel Permission (rôles et permissions)

Les tests utilisent SQLite en mémoire.

## Prérequis

- PHP 8.3 avec les extensions `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `intl`, `pdo_mysql`, `pdo_sqlite`
- Composer 2
- Node.js 22 et npm
- MySQL 8 ou MariaDB 10.11

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Créer la base et un utilisateur MySQL, puis renseigner `.env` :

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=twende_market
DB_USERNAME=twende
DB_PASSWORD=
```

```bash
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

L'application est disponible sur `http://localhost:8000`.

Le développement des assets se lance avec `npm run dev`. `composer run dev` démarre le serveur, les files et Vite ensemble.

## Configuration

| Variable | Rôle |
| --- | --- |
| `APP_LOCALE` | Langue par défaut. `fr` est la langue principale. `en`, `ln` et `sw` sont préparées. |
| `TWENDE_CURRENCY` | Devise par défaut des nouveaux comptes. `CDF` ou `USD`. |
| `SMS_DRIVER` | Canal SMS. `log` enregistre l'envoi sans afficher le code dans les journaux. |
| `SEARCH_DRIVER` | Moteur de recherche. `null` tant que le catalogue n'est pas en place. Le contrat `App\Contracts\ProductSearch` accueillera Meilisearch ou SQL. |
| `GOOGLE_*`, `FACEBOOK_*` | Emplacements pour une connexion sociale ultérieure. Aucun bouton n'est affiché sans identifiants. |

Les couleurs de la marque sont échantillonnées sur le logo officiel et déclarées à la fois dans `config/twende.php` et `resources/css/app.css`. Les composants n'embarquent pas de codes HEX.

En mode sombre, le logo officiel reste sur un fond blanc. Il n'est pas recoloré.

## Base de données

Les migrations créent les utilisateurs, les sessions, le cache, les files, les jetons Sanctum, les rôles, les permissions, les comptes sociaux et les défis OTP.

Les rôles Spatie sont insérés par une migration. L'inscription publique attribue uniquement le rôle `customer`. Un champ `role` envoyé par le navigateur est ignoré.

Rôles : `super_admin`, `admin_manager`, `catalog_manager`, `order_manager`, `finance_manager`, `support_agent`, `marketing_manager`, `moderator`, `vendor`, `vendor_manager`, `vendor_catalog_manager`, `vendor_order_manager`, `delivery_manager`, `delivery_agent`, `customer`.

## Seeders

`php artisan db:seed` crée quatre comptes de démonstration, hors production :

| Rôle | E-mail | Mot de passe |
| --- | --- | --- |
| Super administrateur | admin@twende.market | Twende-Demo-2026 |
| Client | client@twende.market | Twende-Demo-2026 |
| Vendeur | vendeur@twende.market | Twende-Demo-2026 |
| Livreur | livreur@twende.market | Twende-Demo-2026 |
| Catalogue | catalogue@twende.market | Twende-Demo-2026 |
| Responsable livraisons | dispatch@twende.market | Twende-Demo-2026 |

Ces comptes ne doivent pas être utilisés en production. En environnement `production`, le seeder n'ajoute que les rôles.

## Authentification

- Inscription, connexion, déconnexion
- Vérification de l'adresse e-mail
- Réinitialisation et changement du mot de passe
- Téléphone normalisé, prêt pour une vérification OTP
- Sessions régénérées et connexions limitées à 5 tentatives par minute

L'OTP est disponible via `App\Services\Otp\OtpService`. Le code est stocké hashé, expire, et ne peut être consommé qu'une fois. Aucun SMS réel n'est envoyé tant qu'un fournisseur n'est pas branché sur `App\Contracts\SmsGateway`.

## API

Préfixe `/api/v1`, authentification Sanctum, resources, form requests et limitation de débit.

- `GET /api/v1/health`
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/user`

## Tests

```bash
php artisan test
```

La suite couvre l'accueil et le logo, l'authentification, les rôles, l'API, les langues et l'OTP.

## Build

```bash
npm run build
```

Les fichiers compilés sont écrits dans `public/build`.

## Déploiement

1. Cloner le dépôt et installer les dépendances PHP et npm.
2. Renseigner `.env` avec `APP_ENV=production`, `APP_DEBUG=false` et une clé d'application neuve.
3. Configurer MySQL, le mail et, plus tard, le stockage S3 ou R2.
4. Lancer `php artisan migrate --force`.
5. Lancer `npm run build`.
6. Servir le dossier `public` derrière HTTPS.
7. Exécuter les files avec `php artisan queue:work` lorsque des travaux asynchrones seront ajoutés.

Ne pas lancer le seeder de démonstration en production.

Redis est prévu dans `.env.example` (`REDIS_*`) pour le cache et les files. Le stockage local reste le disque par défaut, compatible avec un passage ultérieur vers S3 ou Cloudflare R2.
