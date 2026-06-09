FROM php:8.4-fpm-bookworm

ARG UID=1000
ARG GID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        bash \
        ca-certificates \
        curl \
        git \
        libicu-dev \
        libsqlite3-dev \
        libzip-dev \
        pkg-config \
        unzip \
        zip \
    && docker-php-ext-install bcmath intl pcntl pdo_mysql pdo_sqlite zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && groupadd -g ${GID} xepanel \
    && useradd -u ${UID} -g ${GID} -m -s /bin/bash xepanel \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY .docker/app/php.ini /usr/local/etc/php/conf.d/99-xepanel.ini
COPY .docker/app/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY .docker/app/entrypoint.sh /usr/local/bin/xepanel-entrypoint

RUN chmod +x /usr/local/bin/xepanel-entrypoint

ENTRYPOINT ["xepanel-entrypoint"]
CMD ["php-fpm"]
