FROM ubuntu:mantic as builder

ARG DEBIAN_FRONTEND=noninteractive
ENV TZ=Etc/UTC
ENV APP_ENV=prod
ENV APP_DEBUG=0

RUN apt-get update \
	&& apt-get install -y --no-install-recommends --no-install-suggests \
		git \
		php-cli \
		php-xml \
		nodejs \
		npm \
		composer \
		unzip \
		jq

RUN mkdir -pv /proj
WORKDIR /proj
COPY . /proj

RUN jq --argjson new true '.extra.runtime.disable_dotenv = $new' composer.json > composer.json.bkp \
	&& mv composer.json.bkp composer.json

RUN composer install \
	--no-interaction \
	--no-dev \
	--optimize-autoloader \
	--no-scripts \
	--prefer-dist

RUN npm install
RUN npm run build
RUN npm prune --production

FROM ubuntu:mantic

ARG DEBIAN_FRONTEND=noninteractive
ENV TZ=Etc/UTC
ENV APP_ENV=prod
ENV APP_DEBUG=0

RUN apt-get update \
	&& apt-get upgrade -y \
	&& apt-get install -y --no-install-recommends --no-install-suggests \
		apache2 \
		curl \
		php \
		php-xml \
		php-pgsql \
		libapache2-mod-php \
		cron

# don't know why certs are broken, but this fixes it
RUN apt-get install -y --reinstall ca-certificates

# RUN a2enmod php8.2
RUN a2enmod rewrite

COPY --from=builder /proj /var/www/

RUN mkdir -pv /var/www/var
RUN chown --recursive www-data:www-data /var/www/var 

# set apache config
RUN sed -i 's;DocumentRoot /var/www/html;DocumentRoot /var/www/public;g' \
	/etc/apache2/sites-available/000-default.conf
# log to stdout/stderr
RUN sed -i 's;ErrorLog ${APACHE_LOG_DIR}/error.log;ErrorLog /dev/stderr;g' \
	/etc/apache2/sites-available/000-default.conf
RUN sed -i 's;CustomLog ${APACHE_LOG_DIR}/access.log combined;ErrorLog /dev/stdout;g' \
	/etc/apache2/sites-available/000-default.conf

RUN sed -i 's;AllowOverride None;AllowOverride All;g' \
	/etc/apache2/apache2.conf

# config php to populate $_ENV global variable
RUN sed -i 's;variables_order = "GPCS";variables_order = "EGPCS";g' /etc/php/8.2/apache2/php.ini

EXPOSE 80

# on every start clean and rebuild cache
CMD ["sh", "-c", "php /var/www/bin/console cache:clear && /usr/sbin/apache2ctl -D FOREGROUND"]
