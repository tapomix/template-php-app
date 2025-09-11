# Multi stages Dockerfile

### BASE ###

FROM dunglas/frankenphp:php8.3.23 AS fkphp_base

ARG USER=app
ARG UID=1000
ARG GID=${UID}

ENV COMPOSER_HOME=/usr/local/share/composer

# install composer & extensions
RUN set -eux; \
    install-php-extensions \
        @composer \
        apcu \
        gd \
        intl \
        pdo_pgsql \
        sockets \
        zip

COPY .docker/php/app.ini $PHP_INI_DIR/conf.d/10-app.ini

RUN groupadd -g ${GID} ${USER}; \
    # -m to create home directory required for devcontainer
    useradd -m -u ${UID} -g ${GID} ${USER}; \
    # remove default capabilities
    setcap -r /usr/local/bin/frankenphp; \
    # give write access to /data/caddy + /config/caddy
    chown -R ${USER}:${USER} /data/caddy /config/caddy

# add composer bin to PATH (to use framework cli installer commands)
ENV PATH="${COMPOSER_HOME}/vendor/bin:${PATH}"

### DEV ###
FROM fkphp_base AS fkphp_dev

# install xdebug
RUN set -eux; \
    install-php-extensions \
        xdebug

# add git (vscode claim git to work in devcontainer)
RUN apt-get update \
    && apt-get install -y \
        git \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini
COPY .docker/php/app-dev.ini $PHP_INI_DIR/conf.d/20-app-dev.ini

USER ${USER}

WORKDIR /app/code

### BUILD ###
FROM fkphp_base AS build

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --link code/composer.* code/symfony.* ./

RUN --mount=type=secret,id=composer-auth,env=COMPOSER_AUTH composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

COPY --link code .

RUN composer run-script --no-dev deploy;

### PROD ###
FROM fkphp_base AS fkphp_prod

RUN cp $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini
COPY .docker/php/app-prod.ini $PHP_INI_DIR/conf.d/20-app-prod.ini

COPY --from=build /app .

# enable worker mode ( require package runtime/frankenphp-symfony )
# ENV FRANKENPHP_CONFIG="worker ./public/index.php"
# ENV APP_RUNTIME="Runtime\\FrankenPhpSymfony\\Runtime"

RUN mkdir -p var/cache /var/log \
    && chown -R ${USER}:${USER} var

USER ${USER}
