#!/usr/bin/env bash
set -euo pipefail

echo "==> Déploiement SymfoConnect en production"

# 1. Récupérer le dernier code
git pull origin main

# 2. Installer les dépendances sans les packages de dev
composer install --no-dev --optimize-autoloader

# 3. Vider et réchauffer le cache
APP_ENV=prod php bin/console cache:clear
APP_ENV=prod php bin/console cache:warmup

# 4. Appliquer les migrations sans confirmation interactive
APP_ENV=prod php bin/console doctrine:migrations:migrate --no-interaction

# 5. Générer les clés JWT si elles n'existent pas encore
if [ ! -f config/jwt/private.pem ]; then
    php bin/console lexik:jwt:generate-keypair
fi

# 6. Ajuster les permissions sur var/
chmod -R 775 var/

echo "==> Déploiement terminé avec succès !"
