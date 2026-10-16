FROM php:8.4-cli

ARG USER_UID=1000
ARG USER_GID=1000
ARG NODE_VERSION=24

WORKDIR /app

ENV DEBIAN_FRONTEND noninteractive

RUN apt update && apt install curl zip unzip git nano gnupg -y \
    && apt update \
    && apt install sqlite3 libsqlite3-dev libcurl4-openssl-dev libzip-dev zlib1g-dev libpng-dev libldap2-dev \
        libonig-dev unixodbc-dev libicu-dev unixodbc-dev supervisor -y \
    && docker-php-ext-install intl ldap sockets zip opcache pdo_mysql pcntl gd ctype curl

RUN curl -sLS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

RUN curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_VERSION.x nodistro main" > /etc/apt/sources.list.d/nodesource.list \
    && apt update && apt install -y nodejs && npm install -g npm

RUN groupadd --force -g $USER_GID laravel  \
    && useradd -ms /bin/bash --no-user-group -g $USER_GID -u $USER_UID laravel \
    && apt -y autoremove && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY start-container /usr/local/bin/start-container
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY php.ini /etc/php/8.4/cli/conf.d/99-sail.ini
RUN chmod +x /usr/local/bin/start-container

EXPOSE 8000

ENTRYPOINT ["start-container"]
