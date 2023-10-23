FROM phpswoole/swoole:5.1-php8.2

ENV LIBRDKAFKA_VERSION v2.2.0
ENV BUILD_DEPS \
        autoconf \
        build-essential \
        git \
        libpcre3-dev \
        python-is-python3 \
        libzip-dev \
        zip \
        git \
        libpq-dev \
        inotify-tools

RUN apt-get clean && apt-get update \
        && apt-get install -y ${BUILD_DEPS} \ 
        && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
        && docker-php-ext-install zip pgsql pdo_pgsql    

RUN cd /tmp \
    && git clone \
        --branch ${LIBRDKAFKA_VERSION} \
        --depth 1 \
        https://github.com/edenhill/librdkafka.git \
    && cd librdkafka \
    && ./configure \
    && make \
    && make install \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka \
    && rm -rf /tmp/librdkafka \
    && apt remove -y build-essential \
    && apt autoremove -y

COPY target / 

ENV TZ=Europe/Athens
RUN mv /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN sed -ri -e 's!;date.timezone =!date.timezone = "Europe/Athens"!g' /usr/local/etc/php/php.ini