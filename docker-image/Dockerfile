FROM ubuntu:18.04

LABEL MAINTAINER Kerron Gordon <kgpsounds.com@gmail.com>
LABEL MAINTAINER Uneeb Khalid <uneeb.khalid@outlook.com>

ENV VERSION=25.0.00
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

WORKDIR /var/www/site/

RUN apt-get update && apt-get -y upgrade && apt install -y --no-install-recommends \
     software-properties-common ca-certificates lsb-release apt-transport-https 
RUN LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php  


# Install apache, PHP, and supplimentary programs. openssh-server, curl, and lynx-cur are for debugging the container.
RUN apt-get update && apt-get -y upgrade && DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    git \
    wget \
    curl \
    lynx \
    php7.4 \
    locales \
    apache2 \
    php7.4-gd \
    php7.4-xml \
    php7.4-zip \
    php7.4-curl \
    php7.4-mysql \
    php7.4-mbstring \
    ca-certificates \
    libapache2-mod-php7.4 \
    && a2enmod php7.4 && a2enmod rewrite

RUN sed -i 's/short_open_tag = Off/short_open_tag = On/' /etc/php/7.4/apache2/php.ini && \
    sed -i 's/magic_quotes_gpc = On/magic_quotes_gpc = Off/g' /etc/php/7.4/apache2/php.ini && \
    sed -i "s/^allow_url_fopen.*$/allow_url_fopen = On/" /etc/php/7.4/apache2/php.ini && \
    sed -i 's/error_reporting = .*$/error_reporting = E_ERROR | E_WARNING | E_PARSE/' /etc/php/7.4/apache2/php.ini

RUN wget -c https://github.com/GibbonEdu/core/archive/v${VERSION}.tar.gz && \
    tar -xzf v${VERSION}.tar.gz && \
    cp -af core-${VERSION}/. ./ && \
    rm -rf core-${VERSION} && rm -rf v${VERSION}.tar.gz && \
    git clone https://github.com/GibbonEdu/core.git ./gibbonCore
    
RUN chmod -R 755 . && chown -R www-data:www-data . && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer &&\
    apt-get remove -y wget && \
    apt-get clean autoclean && \
    apt-get autoremove -y && \
    rm -rfv /var/lib/{apt,dpkg,cache,log}/

ADD apache-config.conf /etc/apache2/sites-enabled/000-default.conf 
ADD .htaccess .

EXPOSE 80
VOLUME /var/www/site/
CMD /usr/sbin/apache2ctl -D FOREGROUND
