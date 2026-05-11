#!/bin/bash
set -eo pipefail

MW=/var/www/html
SENTINEL="$MW/images/.mw-installed"
SETTINGS_SRC=/etc/mediawiki/LocalSettings.php

if [ -z "$MW_ADMIN_PASSWORD" ]; then
    echo "[entrypoint] ERROR: MW_ADMIN_PASSWORD is not set" >&2
    exit 1
fi

first_run() {
    echo "[entrypoint] First run — installing MediaWiki"

    # installer refuses to run if LocalSettings.php exists
    rm -f "$MW/LocalSettings.php"

    php "$MW/maintenance/install.php" \
        --dbserver="$MEDIAWIKI_DB_HOST" \
        --dbname="$MEDIAWIKI_DB_NAME" \
        --dbuser="$MEDIAWIKI_DB_USER" \
        --dbpass="$MEDIAWIKI_DB_PASSWORD" \
        --server="${MW_SERVER:-http://localhost}" \
        --scriptpath="" \
        --pass="$MW_ADMIN_PASSWORD" \
        "altered.wiki" "Admin"

    # replace installer-generated LocalSettings.php with ours
    cp "$SETTINGS_SRC" "$MW/LocalSettings.php"

    echo "[entrypoint] Running database update"
    php "$MW/maintenance/run.php" update --quick

    echo "[entrypoint] Initializing Semantic MediaWiki"
    php "$MW/extensions/SemanticMediaWiki/maintenance/setupStore.php"

    echo "[entrypoint] Configuring CirrusSearch"
    php "$MW/extensions/CirrusSearch/maintenance/UpdateSearchIndexConfig.php"
    php "$MW/extensions/CirrusSearch/maintenance/ForceSearchIndex.php"

    echo "[entrypoint] Setting system messages"
    echo "know what you're taking" \
        | php "$MW/maintenance/run.php" edit --user="Admin" --summary="Initial setup" \
            "MediaWiki:Citizen-footer-desc"
    echo "Psychoactive substances documented by the people who know them." \
        | php "$MW/maintenance/run.php" edit --user="Admin" --summary="Initial setup" \
            "MediaWiki:Citizen-footer-tagline"

    echo "-" \
        | php "$MW/maintenance/run.php" edit --user="Admin" --summary="Initial setup" \
            "MediaWiki:Aboutsite"

    touch "$SENTINEL"
    echo "[entrypoint] Setup complete"
}

if [ ! -f "$SENTINEL" ]; then
    first_run
else
    # upgrade path: sync LocalSettings and apply any schema changes
    cp "$SETTINGS_SRC" "$MW/LocalSettings.php"
    echo "[entrypoint] Running database update"
    php "$MW/maintenance/run.php" update --quick
fi

exec docker-php-entrypoint "$@"
