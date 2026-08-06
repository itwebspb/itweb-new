# Base image for the 1C-Bitrix "Управление сайтом: Бизнес" site.
# Stable system dependencies only. Repository configuration and the Bitrix
# database are provisioned by .cursor/install.sh after checkout.
FROM ubuntu:24.04

ENV DEBIAN_FRONTEND=noninteractive

# Core tooling + Apache (mod_php) + PHP 8.3 with the extensions the Bitrix
# core / Aspro.Max solution require + MariaDB + Composer.
RUN apt-get update \
	&& apt-get install -y --no-install-recommends \
		ca-certificates curl git sudo unzip openssh-client rsync \
		apache2 libapache2-mod-php8.3 \
		php8.3-cli php8.3-common php8.3-mysql php8.3-mbstring php8.3-xml \
		php8.3-curl php8.3-gd php8.3-zip php8.3-opcache php8.3-intl \
		php8.3-bcmath php8.3-soap php8.3-ldap php8.3-apcu \
		mariadb-server mariadb-client \
		composer \
	&& a2enmod rewrite headers expires php8.3 \
	&& rm -rf /var/lib/apt/lists/*

# The environment runs as the "ubuntu" user; give it passwordless sudo so the
# install/start scripts can manage system services.
RUN id -u ubuntu >/dev/null 2>&1 || useradd -m -s /bin/bash -u 1000 ubuntu \
	&& echo 'ubuntu ALL=(ALL) NOPASSWD:ALL' > /etc/sudoers.d/90-ubuntu \
	&& chmod 0440 /etc/sudoers.d/90-ubuntu

USER ubuntu
