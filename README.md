petit message d'excsue: j'ai tout fait en local comme un singouin et dcp j'ai init le repo dernière minute sauf que ducoup y'a pas les commits T.T 

# NightMarket

## Nicolas Hoff

## Description

Plateforme e-commerce sur le thème Valorant

## Installation

```bash
git clone git@github.com:aphshir/ProjAvence.git
cd ProjSecretAvence

composer install

cp .env .env.local

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load

symfony serve
```

## Identifiants de connexion

### Administrateur
- **Email** : admin@nightmarket.com
- **Mot de passe** : admin123

### Utilisateurs de test
- **Email** : user1@test.com à user25@test.com
- **Mot de passe** : password
