FROM composer:2 AS composer

FROM mediawiki:1.44

COPY --from=composer /usr/bin/composer /usr/bin/composer

# System deps needed for git clones, composer, and phpredis
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

RUN pecl install redis && docker-php-ext-enable redis

WORKDIR /var/www/html

# --- Composer-managed extensions --------------------------------------------
# SMW, SRF, PageForms, Maps, DynamicPageList4
COPY composer.local.json ./composer.local.json
RUN composer update --no-dev --no-interaction 2>&1

# --- Git-cloned extensions --------------------------------------------------

# Elastica (ElasticSearch PHP client wrapper, required by CirrusSearch)
RUN git clone --depth=1 --branch REL1_44 \
    https://github.com/wikimedia/mediawiki-extensions-Elastica.git \
    extensions/Elastica \
 && cd extensions/Elastica \
 && composer install --no-dev --no-interaction 2>&1

# CirrusSearch (full-text search backend)
RUN git clone --depth=1 --branch REL1_44 \
    https://github.com/wikimedia/mediawiki-extensions-CirrusSearch.git \
    extensions/CirrusSearch

# PDFEmbed (inline PDF viewer for open-access research papers)
RUN git clone --depth=1 \
    https://github.com/wikimedia/mediawiki-extensions-PDFEmbed.git \
    extensions/PDFEmbed

# SemanticDrilldown (faceted browse interface over SMW properties)
# Canonical repo is under the SemanticMediaWiki org, not wikimedia/
RUN git clone --depth=1 \
    https://github.com/SemanticMediaWiki/SemanticDrilldown.git \
    extensions/SemanticDrilldown

# --- Skin -------------------------------------------------------------------

RUN git clone --depth=1 \
    https://github.com/StarCitizenTools/mediawiki-skins-Citizen.git \
    skins/Citizen

# --- Custom AltchaCaptcha extension -----------------------------------------

RUN git clone --depth=1 \
    https://github.com/Altered-Wiki/AltchaCaptcha.git \
    extensions/AltchaCaptcha

# --- Permissions ------------------------------------------------------------

RUN chown -R www-data:www-data extensions/AltchaCaptcha skins/Citizen

# --- Static assets ----------------------------------------------------------

COPY assets/logo.svg /var/www/html/logo.svg
COPY assets/cc-by-sa.png /var/www/html/cc-by-sa.png

# --- Apache rewrite for clean URLs ------------------------------------------

COPY config/apache-rewrite.conf /etc/apache2/conf-enabled/mediawiki-rewrite.conf
RUN a2enmod rewrite

# --- PHP configuration ------------------------------------------------------

RUN echo "display_errors = Off" > /usr/local/etc/php/conf.d/errors.ini \
 && echo "log_errors = On" >> /usr/local/etc/php/conf.d/errors.ini

# --- Entrypoint -------------------------------------------------------------

ENTRYPOINT ["/usr/local/bin/docker-entrypoint-custom.sh"]
CMD ["apache2-foreground"]
