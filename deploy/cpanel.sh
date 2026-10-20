#!/usr/bin/env bash
set -euo pipefail
umask 027

# This deployment deliberately cannot target geic.in/public_html.
readonly PRIVATE_ROOT=/home2/geicic3c/indore-estates-apps
readonly WEB_ROOT=/home2/geicic3c/indore-estates.geic.in
readonly SHARED_ROOT="$PRIVATE_ROOT/shared"
readonly PHP_BIN=/opt/cpanel/ea-php83/root/usr/bin/php
readonly SOURCE_ROOT="$(pwd -P)"

if [[ "$SOURCE_ROOT" != /home2/geicic3c/repositories/indoreestates ]]; then
    echo 'Refusing deployment from an unexpected repository path.' >&2
    exit 1
fi
if [[ ! -x "$PHP_BIN" || ! -d "$WEB_ROOT" || -L "$WEB_ROOT" ]]; then
    echo 'Expected PHP 8.3 or isolated domain document root is missing.' >&2
    exit 1
fi
if [[ ! -s "$SHARED_ROOT/.env" || ! -f "$SHARED_ROOT/.master-import-complete" ]]; then
    echo 'Configure private shared/.env and complete the Option 3 master import before publishing.' >&2
    exit 1
fi
if [[ -L "$PRIVATE_ROOT" || -L "$SHARED_ROOT" ]]; then
    echo 'Refusing unexpected symlinks for private deployment roots.' >&2
    exit 1
fi

COMPOSER_BIN="$(command -v composer || true)"
if [[ -z "$COMPOSER_BIN" && -f /opt/cpanel/composer/bin/composer ]]; then
    COMPOSER_BIN=/opt/cpanel/composer/bin/composer
fi
if [[ -z "$COMPOSER_BIN" ]]; then
    echo 'The host must enable Composer before deployment.' >&2
    exit 1
fi

readonly RELEASE_ID="$(date -u +%Y%m%dT%H%M%SZ)-$(git rev-parse --short=12 HEAD)"
readonly RELEASE_ROOT="$PRIVATE_ROOT/releases/$RELEASE_ID"
mkdir -p "$RELEASE_ROOT" "$SHARED_ROOT/media/private" "$SHARED_ROOT/media/public"
chmod 600 "$SHARED_ROOT/.env"
ln -s "$SHARED_ROOT" "$RELEASE_ROOT/shared"

for OPTION in option-1 option-2 option-3; do
    APP_SOURCE="$SOURCE_ROOT/options/$OPTION"
    if [[ "$OPTION" == option-3 ]]; then
        APP_SOURCE="$SOURCE_ROOT"
    fi
    APP_TARGET="$RELEASE_ROOT/$OPTION"
    mkdir "$APP_TARGET"
    for SOURCE_DIRECTORY in app bootstrap config database resources routes public; do
        mkdir -p "$APP_TARGET/$SOURCE_DIRECTORY"
        if [[ "$SOURCE_DIRECTORY" == database ]]; then
            for DATABASE_DIRECTORY in migrations factories seeders; do
                cp -a "$APP_SOURCE/database/$DATABASE_DIRECTORY" "$APP_TARGET/database/"
            done
        elif [[ "$SOURCE_DIRECTORY" == bootstrap ]]; then
            cp "$APP_SOURCE/bootstrap/app.php" "$APP_SOURCE/bootstrap/providers.php" "$APP_TARGET/bootstrap/"
            mkdir -p "$APP_TARGET/bootstrap/cache"
        else
            cp -a "$APP_SOURCE/$SOURCE_DIRECTORY/." "$APP_TARGET/$SOURCE_DIRECTORY/"
        fi
    done
    cp "$APP_SOURCE/artisan" "$APP_SOURCE/composer.json" "$APP_SOURCE/composer.lock" "$APP_TARGET/"
    mkdir -p "$SHARED_ROOT/runtime/$OPTION/framework/cache/data" "$SHARED_ROOT/runtime/$OPTION/framework/sessions" "$SHARED_ROOT/runtime/$OPTION/framework/views" "$SHARED_ROOT/runtime/$OPTION/logs"
    ln -s "$SHARED_ROOT/runtime/$OPTION" "$APP_TARGET/storage"
    printf 'APP_URL=https://indore-estates.geic.in/%s\nSESSION_COOKIE=indoreestate_%s_session\nSESSION_PATH=/%s\n' "$OPTION" "${OPTION//-/_}" "$OPTION" > "$APP_TARGET/.env"
    chmod 600 "$APP_TARGET/.env"

    (
        cd "$APP_TARGET"
        "$PHP_BIN" "$COMPOSER_BIN" install --no-dev --prefer-dist --no-interaction --no-progress --no-scripts --no-plugins --optimize-autoloader
        "$PHP_BIN" artisan package:discover --no-interaction
        "$PHP_BIN" -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); if (config("database.default") !== "mysql" || config("database.connections.mysql.database") !== "geicic3c_indore_estates" || config("app.debug") || !config("app.key")) { fwrite(STDERR, "Production database or security configuration failed validation.\n"); exit(1); }'
        "$PHP_BIN" artisan config:cache --no-interaction
        "$PHP_BIN" artisan route:cache --no-interaction
        "$PHP_BIN" artisan view:cache --no-interaction
    )
done

# Only additive pending migrations run; never reset or reseed production data.
(cd "$RELEASE_ROOT/option-3" && "$PHP_BIN" artisan migrate --force --no-interaction)

for OPTION in option-1 option-2 option-3; do
    PUBLIC_TARGET="$WEB_ROOT/$OPTION"
    if [[ -e "$PUBLIC_TARGET" && ! -L "$PUBLIC_TARGET" ]]; then
        echo "Refusing to replace existing non-managed directory: $PUBLIC_TARGET" >&2
        exit 1
    fi
    if [[ -L "$PUBLIC_TARGET" ]]; then
        CURRENT_TARGET="$(readlink -f "$PUBLIC_TARGET")"
        if [[ "$CURRENT_TARGET" != "$PRIVATE_ROOT/releases/"*"/$OPTION/public" ]]; then
            echo 'Refusing to replace a symlink outside managed releases.' >&2
            exit 1
        fi
    fi
done

for OPTION in option-1 option-2 option-3; do
    ln -s "$RELEASE_ROOT/$OPTION/public" "$WEB_ROOT/.$OPTION-$RELEASE_ID"
    mv -Tf "$WEB_ROOT/.$OPTION-$RELEASE_ID" "$WEB_ROOT/$OPTION"
done
cp "$SOURCE_ROOT/deploy/landing.html" "$WEB_ROOT/index.html"
echo "Published $RELEASE_ID to indore-estates.geic.in only. Previous releases and shared data were preserved."
