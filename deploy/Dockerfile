# PHP 5.6 + Apache (mod_php) depuis ppa:ondrej/php, qui publie les correctifs
# php2shell (CVE-2026-109618 / CVE-2026-109619) pour 5.6. L'image officielle
# prestashop/base:5.6-apache ne sera jamais corrigée.
FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive TZ=America/Toronto

RUN apt-get update \
 && apt-get install -y --no-install-recommends software-properties-common gpg-agent ca-certificates tzdata \
 && add-apt-repository -y ppa:ondrej/php \
 && apt-get update \
 && apt-get install -y --no-install-recommends \
      apache2 libapache2-mod-php5.6 \
      php5.6-mysql php5.6-gd php5.6-curl php5.6-mbstring php5.6-xml \
      php5.6-zip php5.6-mcrypt php5.6-intl php5.6-soap \
      msmtp \
 && a2enmod rewrite remoteip headers expires deflate setenvif \
 && a2dissite 000-default \
 && rm -rf /var/lib/apt/lists/*

COPY php-hardening.ini /etc/php/5.6/apache2/conf.d/99-hardening.ini
COPY apache-site.conf /etc/apache2/sites-available/amimoucheur.conf
COPY apache-mpm.conf /etc/apache2/conf-enabled/zz-mpm.conf
RUN a2ensite amimoucheur

EXPOSE 80
CMD ["apache2ctl", "-D", "FOREGROUND"]
